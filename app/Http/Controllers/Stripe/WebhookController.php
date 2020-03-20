<?php

namespace App\Http\Controllers\Stripe;

use App\ConnectCustomer;
use App\Enums\PaymentIntentStatus;
use App\Enums\RefundApplicationStatus;
use App\Enums\RefundStripeStatus;
use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionNewMember;
use App\Events\SubscriptionChanged;
use App\Http\Middleware\VerifyWebhookSignature;
use App\Http\Resources\Subscription as SubscriptionResource;
use App\Invoice;
use App\MyClasses\Support\Facade\Stripe;
use App\Refund;
use App\Sharing;
use App\SharingUser;
use App\Subscription;
use App\Transaction;
use App\Transfer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Create a new WebhookController instance.
     *
     * @return void
     */
    public function __construct()
    {
        logger("****************************");
        logger("INVOCATO WEBHOOK");
        logger("****************************");
        if (config('custom.stripe.webhook.secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * Handle a Stripe webhook call.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

        logger($method);
        logger($payload);

        //WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $response = $this->{$method}($payload);

            //WebhookHandled::dispatch($payload);

            return $response;
        }

        return $this->missingMethod();
    }


    /**
     * Conferma iscrizione alla sottoscrizione
     *
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        /* FINO A QUANDO RISOLVI PROBLEMA ACCOUNT_ID
        $object = $payload['data']['object'];

        logger("1");

        $user = User::where('pl_customer_id', $object['customer'])->firstOrFail();
        Auth::login($user);

        logger("2");

        DB::transaction(function() use($object){

            logger("3");

            $account_id = Subscription::findOrFail($object['subscription'])->sharingUser->sharing->owner->pl_account_id;

            $invoice = Invoice::create([
                'stripe_id' => $object['id'],
                'customer_id' => $object['customer'],
                'account_id' => $account_id,
                'subscription_id' => $object['subscription'],
                'payment_intent' => $object['payment_intent'],
                'total' => $object['total'],
                'currency' => $object['currency'],
                'last4' => '1234'
            ]);

            logger("4");

            $invoice->transactions()->create();

            logger("5");

            // creo dei refunds fake
            //$invoice->refunds()->create()->transactions()->create();

        });
        */


        /*
        $userSharing = Subscription::where('id', $subscription_id)->firstOrFail()->sharingUser;

        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        $stateMachine = \StateMachine::get($userSharing, 'sharing');
        $transition = 'pay';

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $userSharing->save();
        }
        */

        /*
        $plan = $payload['data']['object']['lines']['data'][0]['plan']['id'];

        $account_id = Sharing::where('stripe_plan', $plan)->firstOrFail()->owner->pl_account_id;
        $amount = 1000;//$object['total'];
        $currency = $object['currency'];
        $charge = $object['charge'];

        $transfer = \Stripe\Transfer::create([
            "amount" => $amount,
            "currency" => $currency,
            "source_transaction" => $charge,
            "destination" => $account_id,
        ]);
        */

        //logger("6");

        //logger('Payment successffull');

        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload)
    {
        logger('Payment failed');
        //logger($payload);
    }

    protected function handleInvoicePaymentActionRequired(array $payload)
    {
        logger('Payment action required');
        //logger($payload);
    }


    protected function handleCustomerSubscriptionCreated(array $payload)
    {

        $sharingSubscription = $payload['data']['object'];

        $subscription = DB::transaction(function() use ($sharingSubscription) {

            // Retrieve the user
            $user = User::where('pl_customer_id', $sharingSubscription['customer'])->firstOrFail();
            Auth::login($user);

            // Retrieve the sharing
            $sharing = Sharing::where('stripe_plan', $sharingSubscription['plan']['id'])->firstOrFail();


            $sharingUser = $user->sharings()->find($sharing->id)->sharing_status;

            // Create the subscription
            $subscription = $sharingUser->subscription()->create([
                'id' => $sharingSubscription['id'],
                'status' => SubscriptionStatus::getValue($sharingSubscription['status']),
                'current_period_end_at' => $sharingSubscription['current_period_end']
            ]);

            $transition = 'pay';
            if ($sharingUser->canApply($transition)) {
                $sharingUser->apply($transition);
                $sharingUser->save();
            };

            return $subscription;
        });

        event(New SubscriptionChanged($sharingSubscription, $subscription));

        return $this->successMethod();
    }


    /**
     * Cambio di stato della sottoscrizione
     *
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $sharingSubscription = $payload['data']['object'];

        $subscription = DB::transaction(function() use ($sharingSubscription) {

            $subscription = Subscription::findOrFail($sharingSubscription['id']);

            $subscription->update([
                'status' => SubscriptionStatus::getValue($sharingSubscription['status']),
                'cancel_at_period_end' => $sharingSubscription['cancel_at_period_end'],
                'ended_at' => $sharingSubscription['ended_at'],
                'current_period_end_at' => $sharingSubscription['current_period_end']
            ]);

            $sharingUser = $subscription->sharingUser;
            $user = User::findOrFail($sharingUser->user_id);
            Auth::login($user);

            $transition = 'pay';
            if ($sharingUser->canApply($transition)) {
                $sharingUser->apply($transition);
                $sharingUser->save();
            };

            return $subscription;
        });

        event(New SubscriptionChanged($sharingSubscription, $subscription));

        return $this->successMethod();
    }

    /**
     * Conferma cancellazione della sottoscrizione
     *
     */

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {

        $subscription_id = $payload['data']['object']['id'];
        $userSharing = Subscription::where('id', $subscription_id)->firstOrFail()->sharingUser;

        $stateMachine = \StateMachine::get($userSharing, 'sharing');


        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        DB::transaction(function() use ($stateMachine, $userSharing, $payload) {
            $transition = 'left';

            if ($stateMachine->can($transition)) {
                $stateMachine->apply($transition);
                $userSharing->save();
            }

            $this->updateSubscription($userSharing->subscription, $payload['data']['object']);

            logger("Avvisare della cancellazione sia admin che user, avvertire admin di cambiare le password");
        });

        return $this->successMethod();
    }

    protected function handleChargeRefunded(array $payload)
    {
        $object = $payload['data']['object']['refunds']['data'][0];

        Invoice::where('payment_intent', $object['payment_intent'])->firstOrFail()->refunds()->firstOrFail()->update([
            'stripe_id' => $object['id'],
            'status' => RefundStripeStatus::getValue($object['status'])
        ]);

        return $this->successMethod();
    }

    protected function handleChargeRefundUpdated(array $payload)
    {
        logger("Dovrebbe essere invocato solo quando il rimborso fallisce da parte di stripe");

        return $this->successMethod();
    }

    protected function handleTransferCreated(array $payload)
    {
        $object = $payload['data']['object'];

        Invoice::where('payment_intent', $object['transfer_group'])->firstOrFail()->transfer()->create([
            'stripe_id' => $object['id'],
            'account_id' => $object['destination'],
            'amount' => $object['amount'],
            'currency' => $object['currency'],
        ]);

        // inviare mail all'utente che il denaro è stato trasferito

        return $this->successMethod();
    }

    /**
     * Handle successful calls on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function successMethod($parameters = [])
    {
        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function missingMethod($parameters = [])
    {
        return new Response;
    }

}
