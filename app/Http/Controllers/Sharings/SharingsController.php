<?php

namespace App\Http\Controllers\Sharings;

use App\Category;
use App\Enums\RenewalStatus;
use App\Enums\SharingApprovationStatus;
use App\Enums\SubscriptionSharingStatus;
use App\Enums\SubscriptionStatus;
use App\Events\SharingCreated;
use App\Events\SharingStatusUpdated;
use App\Http\Requests\CredentialRequest;
use App\Http\Requests\SharingRequest;
use App\Http\Resources\Sharing as SharingResource;
use App\Http\Resources\SharingCollection;
use App\Http\Resources\SubscriptionSharing;
use App\Http\Traits\Utility;
use App\MyClasses\Support\Facade\Stripe;
use App\Sharing;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

        /*\Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        collect(\Stripe\Subscription::all(['limit' => 99])->data)->each(function($item){
            $item->delete();
        });
        */

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");
        \Stripe\Subscription::update('sub_H9dkorjbcN6kWT', [
                'trial_end' => Carbon::now()->addSeconds(10)->timestamp,
                'prorate' => false
            ]
        );
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

    public function subscribe(Request $request, Sharing $sharing)
    {
        $user = Auth::user();
        $userSharing = $user->sharings()->find($sharing->id)->sharing_status;
        $stateMachine = \StateMachine::get($userSharing, 'sharing');
        $sharingUser = $user->sharings()->find($sharing->id)->sharing_status;

        if($stateMachine->can('pay') || $user->can('restore', $userSharing)) {

            $status = null;
            $clientSecret = null;
            $subId = null;

            if(!$userSharing->subscription){ // If an incomplete subscription exist, manage them, otherwise create newone
                $subscription = Stripe::createSubscription($sharing, $user);

                // Create the subscription
                $sharingUser->subscription()->create([
                    'id' => $subscription->id,
                    'status' => SubscriptionStatus::getValue($subscription->status),
                    'current_period_end_at' => $subscription->current_period_end
                ]);

                switch ($subscription->status){
                    case 'active':
                        if($subscription->latest_invoice->status === 'paid' && $subscription->latest_invoice->payment_intent->status === 'succeeded'){
                            $status = SubscriptionSharingStatus::succeeded;
                        }

                        $transition = 'pay';
                        if ($sharingUser->canApply($transition)) {
                            $sharingUser->apply($transition);
                            $sharingUser->save();
                        };

                        break;
                    case 'trialing':
                        // Doesn't handle this case
                        abort(403);
                        break;
                    case 'incomplete':
                        if($subscription->latest_invoice->status === 'open'){
                            if($subscription->latest_invoice->payment_intent->status === 'requires_payment_method'){
                                $status = SubscriptionSharingStatus::requires_payment_method;
                            }else if($subscription->latest_invoice->payment_intent->status === 'requires_action'){
                                $status = SubscriptionSharingStatus::requires_action;
                            }
                        }
                        break;
                }

                $subId = $subscription->id;
                $clientSecret = $subscription->latest_invoice->payment_intent->client_secret;

            }else{

                $invoice = Stripe::payInvoice($userSharing->subscription->id);

                // Update the subscription
                $userSharing->subscription->update([
                    'status' => SubscriptionStatus::getValue($invoice->subscription->status),
                    'current_period_end_at' => $invoice->subscription->current_period_end
                ]);

                switch ($invoice->payment_intent->status){
                    case 'succeeded':
                        $status = SubscriptionSharingStatus::succeeded;

                        $transition = 'pay';
                        if ($sharingUser->canApply($transition)) {
                            $sharingUser->apply($transition);
                            $sharingUser->save();
                        };

                        break;
                    case 'requires_payment_method':
                        $status = SubscriptionSharingStatus::requires_payment_method;
                        break;
                    case 'requires_action':
                        $status = SubscriptionSharingStatus::requires_action;
                        break;
                }

                $clientSecret = $invoice->payment_intent->client_secret;
                $subId = $userSharing->subscription->id;
            }

            return new SubscriptionSharing([
                'status' => $status,
                'client_secret' => $clientSecret,
                'sub_id' => $subId
            ]);

        }else{
            abort(403);
        }

    }

    public function confirm3dSecure()
    {

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