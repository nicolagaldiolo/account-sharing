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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

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
        switch ($param){
            case 'pending':
                $sharings = SharingResource::collection(Auth::user()->sharings()->with(['owner'])->pending()->paginate(config('custom.paginate')));
                break;
            case 'approved':
                $sharings = SharingResource::collection(Auth::user()->sharings()->with('owner')->approved()->paginate(config('custom.paginate')));
                break;
            case 'owner':
                $sharings = SharingResource::collection(Auth::user()->sharingOwners()->with('users')->paginate(config('custom.paginate')));
                break;
            case 'joined':
                $sharings = SharingResource::collection(Auth::user()->sharings()->with('owner')->joined()->paginate(config('custom.paginate')));
                break;
            default:

                // Create a custom pagination (https://github.com/laravel/framework/issues/3105)

                $perPage = config('custom.paginate');
                $currentPage = $request->input('page', 1);
                $path_url = URL::to("/{$request->route()->getPrefix()}/sharings");
                $paginationOption = ['path' => $path_url];

                $sharings = Sharing::with('owner')->public()->get();

                $totalElements = $sharings->count();
                $sharings_paginate = $sharings->slice(($currentPage - 1) * $perPage, $perPage);

                $lengthAwarePaginator = new \Illuminate\Pagination\LengthAwarePaginator($sharings_paginate, $totalElements, $perPage, $currentPage, $paginationOption);
                $sharings = SharingResource::collection($lengthAwarePaginator);
                break;
        }

        return $sharings;
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

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $customers = collect(\Stripe\Subscription::all(['limit' => 99])->data);
        if($customers->isNotEmpty()){
            $customers->each(function($item){
                $item->delete();
            });
        }

        /*
        $user = Auth::login(User::find(1));

        $sharing = Sharing::find(1);

        return $sharing;

        //$sharing = Sharing::with(['members.subscriptions' => function($query){
        //    $query->where('sharing_user_id', 59)->first();
        //}])->first();

        $sharing = Sharing::withCount('members')->havingRaw('`members_count` < `sharings`.`slot`')->get();

        //$sharing->load('members.subscriptions');

        //$account_id = Subscription::findOrFail('sub_GU7NaO7AnZr7so');
        //$test = $account_id->sharingUser->sharing->owner->pl_customer_id;

        return "aaa"; //$sharing;




        //return Stripe::getAccount($user);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        return \Stripe\Account::retrieve(
            'acct_1FottpCGUE69w9KZ'
        );

        //$user->pl_account_id = null;
        //$user->save();

        if (empty($user->pl_account_id)) {
            $account = \Stripe\Account::create([
                'country' => 'GB',
                'email' => $user->email,
                'type' => 'custom',
                "requested_capabilities" => ["card_payments","transfers"],
                'business_type' => 'individual',

                'individual' => [
                    'email' => $user->email,
                    'first_name' => $user->name,
                    'last_name' => $user->surname,
                    'phone' => '+393917568474',
                    'dob' => [
                        'day' => $user->birthday->day,
                        'month' => $user->birthday->month,
                        'year' => $user->birthday->year
                    ],
                    'address' => [
                        'line1' => 'Via Giovanni Caboto',
                        'city' => 'london',
                        'postal_code' => 'WC2H 0HU'
                    ]
                ],
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => request()->ip()
                ],
                'business_profile' => [
                    'mcc' => '5734',
                    'product_description' => ''
                ]
            ]);

            logger($account);

            $user->pl_account_id = $account->id;
            $user->save();

        }
        */
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
        $sharing->load(['members','owner']);

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
    public function update(Request $request, Sharing $sharing, User $user)
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
