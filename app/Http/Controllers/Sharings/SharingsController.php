<?php

namespace App\Http\Controllers\Sharings;

use App\Enums\RenewalStatus;
use App\Enums\SharingStatus;
use App\Http\Requests\CredentialRequest;
use App\Http\Requests\SharingRequest;
use App\Http\Traits\SharingTrait;
use App\Sharing;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\MyClasses\Support\Facade\Stripe;

class SharingsController extends Controller
{
    use SharingTrait;

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
                $sharings = Auth::user()->sharings()->pending()->get();
                break;
            case 'approved':
                $sharings = Auth::user()->sharings()->approved()->get();
                break;
            case 'owner':
                // manipolo i dati tornati raggruppando gli utenti per stato della relazione con sharing(es: pendind: utenti..., joined: utenti...)
                $sharings = $this->getSharingOwners();
                break;
            case 'joined':
                $sharings = Auth::user()->sharings()->joined()->get();
                break;
            default:
                $sharings = Sharing::public()->get();
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

    public function prova(Sharing $sharing)
    {
        $stripeObj = app(Stripe::class);

        // Se ci sono utenti con account Stripe li elimino
        collect($stripeObj->allAccount(['limit' => 99])->data)->each(function($item) use($stripeObj){
            $stripeObj->getAccount($item->id)->delete();
        });

        // Se ci sono Customer li elimino
        collect($stripeObj->allCustomer(['limit' => 99])->data)->each(function($item) use($stripeObj){
            $stripeObj->getCustomer($item->id)->delete();
        });

        $me = User::find(1);

        $stripeAccount = $stripeObj->createAccount([
            'country' => 'IT',
            'email' => $me->email,
            'type' => 'custom',
            'business_type' => 'individual',
            // Before the 2019-09-09 API version, the transfers capability was referred to as platform_payments. If you're using an API version older than 2019-09-09, you need to use platform_payments.
            // For platforms creating connected accounts in Australia, Austria, Belgium, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Ireland, Italy, Latvia, Lithuania, Luxembourg, the Netherlands, New Zealand, Norway, Poland, Portugal, Slovakia, Slovenia, Spain, Sweden, Switzerland, or the United Kingdom, request both the card_payments and transfers capabilities to enable card processing for your connected accounts.
            "requested_capabilities" => ["card_payments", "transfers"],
            'individual' => [
                'email' => $me->email,
                'first_name' => $me->name,
                'last_name' => $me->surname,
                'phone' => '+393917568474',
                'dob' => [
                    'day' => $me->birthday->day,
                    'month' => $me->birthday->month,
                    'year' => $me->birthday->year
                ],
                'address' => [
                    'line1' => 'Via Giovanni Caboto',
                    'city' => 'Verona',
                    'postal_code' => '37068'
                ]
            ],
            'tos_acceptance' => [
                'date' => time(),
                'ip' => request()->ip() // Assumes you're not using a proxy
            ],
            'business_profile' => [
                'mcc' => '4900',
                'url' => 'https://www.google.it'
            ],
        ]);

        $me->stripe_account_id = $stripeAccount->id;
        //$me->stripe_customer_id = $stripeCustomer->id;
        $me->save();

        $payment_intent = \Stripe\PaymentIntent::create([
            'payment_method_types' => ['card'],
            'amount' => 666,
            'currency' => 'eur',
            'transfer_data' => [
                'destination' => $me->stripe_account_id,
            ],
        ]);

        return view('prova', compact('payment_intent'));

        /*
        //dd();
        $stripe = Stripe::make(config('services.stripe.secret'));
        //$customers = $stripe->customers()->all();
        //dd($customers);

        $plan = $stripe->plans()->create([
            'id'                   => 'monthly',
            'name'                 => 'Monthly (30$)',
            'amount'               => 30.00,
            'currency'             => 'USD',
            'interval'             => 'month',
            'statement_descriptor' => 'Monthly Subscription',
        ]);
        dd($plan);

        //return $sharing;
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
        $sharing = Sharing::create($request->validated());
        return Auth::user()->sharings()->save($sharing, [
            'status' => SharingStatus::Joined,
            'owner' => true
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Sharing $sharing)
    {
        return $this->getSharing($sharing);
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
    public function update(Request $request, $id)
    {
        //
    }

    public function transition(Request $request, Sharing $sharing, $transition = null)
    {
        // Cerco la relazione tra utente e sharing, se non esiste la creo
        $user = Auth::user();
        $userSharing = $user->sharings()->find($sharing->id);

        if(!$userSharing) {
            $user->sharings()->attach($sharing->id);
            $userSharing = $user->sharings()->find($sharing->id);
        }

        $sharingStatus = $userSharing->sharing_status;
        $stateMachine = \StateMachine::get($sharingStatus, 'sharing');

        if($transition && $stateMachine->can($transition)) {
            switch ($transition){
                case 'pay':

                    $currentStripeAccount = $sharing->owner->stripe_account_id;

                    if(is_null($user->stripe_customer_id)){
                        // Creo il Customer
                        $stripeCustomer = Stripe::createCustomer([
                            'email' => $user->email,
                            'source' => 'tok_threeDSecure2Required',
                        ], ['stripe_account' => $currentStripeAccount]);
                        $user->stripe_customer_id = $stripeCustomer->id;
                        $user->save();
                    }else{
                        $stripeCustomer = Stripe::getCustomer(
                            $user->stripe_customer_id,
                            ["stripe_account" => $currentStripeAccount]
                        );
                    }

                    $subscription = Stripe::createSubscription([
                        'customer' => $stripeCustomer->id,
                        'items' => [
                            [
                                'plan' => $sharing->stripe_plan,
                            ],
                        ],
                        'expand' => ['latest_invoice.payment_intent'],
                    ],['stripe_account' => $currentStripeAccount]);

                    if($subscription->status === 'active' && $subscription->latest_invoice->payment_intent->status === 'succeeded') {
                        $stateMachine->apply($transition);
                        $sharingStatus->save();
                    }else if($subscription->status === 'incomplete' && $subscription->latest_invoice->payment_intent->status === 'requires_payment_method'){

                        /*\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

                        $payment_method = \Stripe\PaymentMethod::retrieve('pm_1FYeznClCIKljWvssSbEXRww');
                        $payment_method->attach(
                            ['customer' => $stripeCustomer->id],
                            ['stripe_account' => $currentStripeAccount]);

                        $stateMachine->apply($transition);
                        $sharingStatus->save();
                        */

                    }else{
                        abort(500);
                        die($subscription);
                    }


                    break;
                default:
                    $stateMachine->apply($transition);
                    $sharingStatus->save();
                    break;
            }
        }
        return $this->getSharing($userSharing);


    }

    public function transitionUser(Request $request, Sharing $sharing, User $user, $transition)
    {
        $sharing_status = $user->sharings()->findOrFail($sharing->id)->sharing_status;
        $stateMachine = \StateMachine::get($sharing_status, 'sharing');

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $sharing_status->save();
        }
        return $this->getSharingOwners($sharing->id);
    }

    public function renewalAction(Request $request, Sharing $sharing, User $user, $action)
    {
        switch ($action) {
            case 'left':
                $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status->renewals()->whereStatus(RenewalStatus::Pending)->orderBy('id', 'desc')->first()->update([
                    'status' => RenewalStatus::Stopped
                ]);
                break;
            case 'restore':
                $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status->renewals()->whereStatus(RenewalStatus::Stopped)->orderBy('id', 'desc')->first()->update([
                    'status' => RenewalStatus::Pending
                ]);
                break;
        }

        return $this->getSharing($sharing);
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
