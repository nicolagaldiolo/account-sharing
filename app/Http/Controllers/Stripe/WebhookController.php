<?php

namespace App\Http\Controllers\Stripe;

use App\ConnectCustomer;
use App\Enums\PaymentIntentStatus;
use App\Enums\RefundApplicationStatus;
use App\Enums\RefundStripeStatus;
use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Events\SharingSubscription;
use App\Http\Middleware\VerifyWebhookSignature;
use App\Http\Traits\SharingTrait;
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
use function foo\func;

class WebhookController extends Controller
{
    use SharingTrait;
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
        //logger($payload);

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

        logger("6");

        logger('Payment successffull');

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
        logger("Sottoscrizione creata");

        $object = $payload['data']['object'];
        $subscription = Subscription::findOrFail($object['id']);

        //if ($subscription->status === SubscriptionStatus::active && $subscription->sharingUser->status === SharingStatus::Joined) {
        //    event(New SharingSubscription($subscription->sharingUser));
        //};

        DB::transaction(function() use ($user, $sharing, $transition) {

            if(Auth::id() != $user->id) Auth::login($user); // If i run the script off-session (webhook or seed)

            $sharingStatus = $user->sharings()->find($sharing->id)->sharing_status;
            $stateMachine = \StateMachine::get($sharingStatus, 'sharing');

            $sharingStatus->subscription()->create([
                'id' => $stripeSubscription->id,
                'status' => SubscriptionStatus::getValue($stripeSubscription->status),
                'current_period_end_at' => $stripeSubscription->current_period_end
            ]);

            if(SubscriptionStatus::getValue($stripeSubscription->status) === SubscriptionStatus::active){
                $stateMachine->apply($transition);
                $sharingStatus->save();

                logger("Utente è dentro 1");
            }

            $stripeSubscription;


        });







        return $this->successMethod();
    }


    /**
     * Cambio di stato della sottoscrizione
     *
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $object = $payload['data']['object'];

        logger("Sottoscrizione aggiornata");

        $this->updateSubscription($object, 'pay');

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
