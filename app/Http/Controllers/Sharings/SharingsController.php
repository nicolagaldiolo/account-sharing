<?php

namespace App\Http\Controllers\Sharings;

use App\Enums\RefundApplicationStatus;
use App\Enums\RenewalStatus;
use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Requests\CredentialRequest;
use App\Http\Requests\SharingRequest;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Traits\SharingTrait;
use App\Invoice;
use App\Payout;
use App\Refund;
use App\Sharing;
use App\SharingUser;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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



        Auth::login(User::find(6));

        $user = Auth::user();

        //Stripe::getAccount($user);

        //$user->pl_account_id = null;
        //$user->save();

        if (empty($user->pl_account_id)) {
            /*$account = \Stripe\Account::create([
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
            */
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SharingRequest $request)
    {

        $this->authorize('create-sharing');

        // Create the sharing
        $sharing = Sharing::create($request->validated());

        // Create stripe plan
        Stripe::createPlan($sharing);

        // Attach the sharing to user
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

        $subscription = Auth::user()->sharings()->findOrFail($sharing->id)->sharing_status->subscription;


        $response = \Stripe\Subscription::update($subscription->id, [
            'cancel_at_period_end' => !boolval($subscription->cancel_at_period_end),
        ]);

        $this->updateSubscription($subscription, $response->toArray());

        return $this->getSharing($sharing);

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
