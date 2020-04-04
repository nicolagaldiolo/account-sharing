<?php

namespace App\Http\Controllers\Sharings;

use App\Category;
use App\Enums\RenewalStatus;
use App\Enums\SharingApprovationStatus;
use App\Events\SharingCreated;
use App\Events\SharingStatusUpdated;
use App\Http\Requests\CredentialRequest;
use App\Http\Requests\SharingRequest;
use App\Http\Resources\Sharing as SharingResource;
use App\Http\Resources\SharingCollection;
use App\Http\Traits\Utility;
use App\MyClasses\Support\Facade\Stripe;
use App\Sharing;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SharingsController extends Controller
{
    use Utility;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = $request->input('type', '');

        $category = (!empty($request->input('category', ''))) ?
            Category::findOrFail($request->input('category', '')) : '';

        $sharings = (!empty($category)) ? $category->sharings() : Auth::user()->sharings();

        switch ($param){
            case 'pending':
                $data = SharingResource::collection($sharings->with(['owner','renewalFrequency'])->pending()->paginate(config('custom.paginate')));
                break;
            case 'approved':
                $data = SharingResource::collection($sharings->with(['owner','renewalFrequency'])->approved()->paginate(config('custom.paginate')));
                break;
            case 'owner':
                $sharings = (!empty($category)) ? $category->sharings() : Auth::user()->sharingOwners();
                $data = SharingResource::collection($sharings->with(['users','renewalFrequency'])->paginate(config('custom.paginate')));
                break;
            case 'joined':
                $data = SharingResource::collection($sharings->with(['owner','renewalFrequency'])->joined()->paginate(config('custom.paginate')));
                break;
            default:

                // Create a custom pagination (https://github.com/laravel/framework/issues/3105)
                $perPage = config('custom.paginate');
                $currentPage = $request->input('page', 1);
                $path_url = URL::to("/{$request->route()->getPrefix()}/sharings");
                $paginationOption = ['path' => $path_url];

                $sharings = ((!empty($category)) ?
                    $category->sharings() :
                    Sharing::query()
                )->with(['owner','renewalFrequency'])->public()->latest()->get();

                $totalElements = $sharings->count();
                $sharings_paginate = $sharings->slice(($currentPage - 1) * $perPage, $perPage);

                $lengthAwarePaginator = new \Illuminate\Pagination\LengthAwarePaginator($sharings_paginate, $totalElements, $perPage, $currentPage, $paginationOption);
                $data = SharingResource::collection($lengthAwarePaginator);
                break;
        }

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function prova(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SharingRequest $request)
    {
        $category = Category::findOrFail($request->get('category_id'));

        $this->authorize('create-sharing', $category);

        $dataValidated = $request->validated();
        $dataValidated['status'] = ($category->custom) ? SharingApprovationStatus::Pending: SharingApprovationStatus::Approved;
        $dataValidated['image'] = $this->processSharingImage($request);

        // Create the sharing
        $user = Auth::user();
        $sharing = $user->sharingOwners()->create($dataValidated);

        event(New SharingCreated($sharing));

        return new SharingResource($sharing);
    }

    public function changeStatus(Sharing $sharing, $action)
    {
        $action = intval($action);

        $this->authorize('change-sharing-status', [$sharing, $action]);

        // Update the sharing
        $sharing->status = $action;
        $sharing->save();

        event(New SharingStatusUpdated($sharing));

        if($sharing->status === SharingApprovationStatus::Refused){
            $sharing->delete();
        }

        return new SharingResource($sharing);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Sharing $sharing)
    {
        $sharing->load(['members','owner','category', 'renewalFrequency']);

        return new SharingResource($sharing);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function renewalUpdate(Request $request, Sharing $sharing, User $user)
    {
        $this->authorize('update-sharing', [$sharing, $user]);

        $subscription = $sharing->sharingUser($user)->first()->subscription;

        $object = \Stripe\Subscription::update($subscription->id, [
            'cancel_at_period_end' => !boolval($subscription->cancel_at_period_end),
        ]);

        $this->updateSubscription($object->toArray());

        $sharing->load(['members','owner']);

        return new SharingResource($sharing);

    }

    public function update(SharingRequest $request, Sharing $sharing)
    {
        $this->authorize('manage-own-sharing', $sharing);

        $dataValidated = $request->validated();
        $dataValidated['image'] = $this->processSharingImage($request);

        $sharing->update($dataValidated);

        $sharing->load(['members','owner','category']);

        return new SharingResource($sharing);
    }

    public function subscribeRestore(Request $request, Sharing $sharing)
    {
        $user = Auth::user();
        $userSharing = $user->sharings()->find($sharing->id)->sharing_status;
        $stateMachine = \StateMachine::get($userSharing, 'sharing');

        if($stateMachine->can('pay') || $user->can('restore', $userSharing)) {
            // If an incomplete subscription exist, manage them, otherwise create newone
            !$userSharing->subscription ? Stripe::createSubscription($sharing, $user) : Stripe::payInvoice($userSharing->subscription->id);

            return new SharingResource($sharing->load(['members','owner']));
        }else{
            abort(403);
        }

    }

    public function transition(Request $request, Sharing $sharing, $transition = null)
    {

        // Find a relation between user and sharing, i not exist i create them
        $user = Auth::user();
        $userSharing = $user->sharings()->find($sharing->id);

        if(!$userSharing) {
            $user->sharings()->attach($sharing->id);
            $userSharing = $user->sharings()->find($sharing->id);

            // Send Mail to owner and user
            $sharing->owner->notify(new \App\Notifications\SharingUserNewRequest($sharing, $user, true));
            $user->notify(new \App\Notifications\SharingUserNewRequest($sharing, $user));
        }

        $sharingStatus = $userSharing->sharing_status;
        $stateMachine = \StateMachine::get($sharingStatus, 'sharing');

        if($transition && $stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $sharingStatus->save();
        }

        $userSharing->load(['members','owner']);
        return new SharingResource($userSharing);

    }

    public function transitionUser(Request $request, Sharing $sharing, User $user, $transition)
    {
        $sharing_status = $user->sharings()->findOrFail($sharing->id)->sharing_status;
        $stateMachine = \StateMachine::get($sharing_status, 'sharing');

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $sharing_status->save();
        }

        $sharing->load('users');

        return new SharingResource($sharing);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
