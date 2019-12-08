<?php

namespace App\Http\Controllers\Sharings;

use App\Enums\RenewalStatus;
use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Requests\CredentialRequest;
use App\Http\Requests\SharingRequest;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Traits\SharingTrait;
use App\Invoice;
use App\Sharing;
use App\SharingUser;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\MyClasses\Support\Facade\Stripe;
use PhpParser\Builder;

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

    public function prova()
    {

        /*
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
    public function update(Request $request, Sharing $sharing)
    {
        $this->authorize('manage-sharing', $sharing);


        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $subscription = Auth::user()->sharings()->findOrFail($sharing->id)->sharing_status->subscription;


        $response = \Stripe\Subscription::update($subscription->id, [
            'cancel_at_period_end' => !boolval($subscription->cancel_at_period_end),
        ]);

        $this->updateSubscription($subscription, $response->toArray());

        return $this->getSharing($sharing);

    }

    public function transition(Request $request, Sharing $sharing, $transition = null)
    {

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

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
                //case '' :
                //    break;
                default :
                    $stateMachine->apply($transition);
                    $sharingStatus->save();
                    break;
            }
        }
        return $this->getSharing($userSharing);

    }


    public function restore(Request $request, Sharing $sharing)
    {

        $newPaymentMethod = $request->payment_method;

        $user = Auth::user();
        $userSharing = $user->sharings()->find($sharing->id)->sharing_status;

        $this->authorize('can-restore', $userSharing);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $stateMachine = \StateMachine::get($userSharing, 'sharing');

        //if($stateMachine->can('pay')) {

            $payment_method = \Stripe\PaymentMethod::retrieve($newPaymentMethod);

            $customerPaymentMethods = \Stripe\PaymentMethod::all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card',
            ]);

            if(!collect($customerPaymentMethods->data)->pluck('id')->contains($payment_method->id)){
                $payment_method->attach(['customer' => $user->stripe_customer_id]);
            }

            \Stripe\Customer::update($user->stripe_customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $payment_method->id,
                    ],
                ]
            );

            $subscription = \Stripe\Subscription::retrieve($userSharing->subscription->id);

            //logger($subscription);
            //dd();

            if($subscription->status === 'past_due') {
                $invoice = \Stripe\Invoice::retrieve(['id' => $subscription->latest_invoice]);
                try {
                    $invoice->pay();
                } catch (\Exception $e) {
                }
            }

            $subscription = \Stripe\Subscription::retrieve([
                'id' => $userSharing->subscription->id,
                'expand' => [
                    'latest_invoice.payment_intent'
                ]
            ]);

            return $subscription;

        //}else{
        //    abort(500);
        //}

    }

    public function subscribe(Request $request, Sharing $sharing)
    {
        $newPaymentMethod = $request->payment_method;

        $user = Auth::user();
        $userSharing = $user->sharings()->find($sharing->id)->sharing_status;
        $this->authorize('can-subscribe', $userSharing);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $stateMachine = \StateMachine::get($userSharing, 'sharing');

        if($stateMachine->can('pay')) {

            $payment_method = \Stripe\PaymentMethod::retrieve($newPaymentMethod);

            $customerPaymentMethods = \Stripe\PaymentMethod::all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card',
            ]);

            if(!collect($customerPaymentMethods->data)->pluck('id')->contains($payment_method->id)){
                $payment_method->attach(['customer' => $user->stripe_customer_id]);
            }

            \Stripe\Customer::update($user->stripe_customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $payment_method->id,
                    ],
                ]
            );

            // Se esiste già una Subscription incompleta la gestisco
            // altrimenti ne creo una nuova

            try{

                $subscription = \Stripe\Subscription::retrieve($userSharing->subscription->id);

                if($subscription->status !== 'incomplete') {
                    throw new \Exception('Subscription not available');
                }else {
                    $invoice = \Stripe\Invoice::retrieve(['id' => $subscription->latest_invoice]);
                    try {
                        $invoice->pay();
                    } catch (\Exception $e) {
                    }
                }

                $subscription = \Stripe\Subscription::retrieve([
                    'id' => $userSharing->subscription->id,
                    'expand' => [
                        'latest_invoice.payment_intent'
                    ]
                ]);

            }catch(\Exception $e){

                logger($e);

                $subscription = $this->createSubscription($user, $sharing);

            }

            return $subscription;

        }else{
            abort(500);
        }

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
