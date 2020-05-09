<?php

namespace App\Http\Controllers\Stripe;

use App\Enums\PaymentIntentStatus;
use App\Enums\RefundApplicationStatus;
use App\Enums\RefundStripeStatus;
use App\Enums\SubscriptionStatus;
use App\Events\PaymentSucceeded;
use App\Events\SubscriptionDeleted;
use App\Events\SubscriptionNewMember;
use App\Events\SubscriptionPastDue;
use App\Events\SubscriptionProvideService;
use App\Http\Middleware\VerifyWebhookSignature;
use App\Http\Traits\Utility;
use App\Invoice;
use App\MyClasses\Support\Facade\Stripe;
use App\Refund;
use App\Sharing;
use App\Subscription;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    use Utility;

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

        $object = $payload['data']['object'];

        DB::transaction(function() use($object){

            $subscription = Subscription::findOrFail($object['subscription']);

            if($object['billing_reason'] === 'subscription_create'){
                if($subscription->status !== SubscriptionStatus::active){
                    $subscription->update(['status' => SubscriptionStatus::active]);
                }

                $this->applyTransition($subscription->sharingUser, 'pay');
            }

            $sharing = Sharing::where('stripe_plan', $object['lines']['data'][0]['plan']['id'])->firstOrFail();

            $total = $this->convertStripePrice($object['total']);
            $total_less_fee = $this->convertStripePrice($object['lines']['data'][0]['plan']['metadata']['netPrice']);
            $fee = $this->convertStripePrice($object['lines']['data'][0]['plan']['metadata']['fee']);
            $charge = Stripe::chargeRetrieve($object['charge']);
            $invoice = Invoice::create([
                'stripe_id' => $object['id'],
                'customer_id' => $object['customer'],
                'user_id' => $sharing->owner_id,
                'subscription_id' => $object['subscription'],
                'payment_intent' => $object['payment_intent'],
                'service_name' => $sharing->name,
                'total' => $total,
                'total_less_fee' => $total_less_fee,
                'fee' => $fee,
                'currency' => $object['currency'],
                'last4' => $charge->payment_method_details->card->last4
            ]);

            event( New PaymentSucceeded($invoice));
        });

        return $this->successMethod();
    }


    /**
     * Cambio di stato della sottoscrizione
     *
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {

        $stripeSubscription = $payload['data']['object'];

        if($stripeSubscription['status'] === 'past_due'){

            $subscription = Subscription::findOrFail($stripeSubscription['id']);

            $subscription->update([
                'status' => SubscriptionStatus::getValue($stripeSubscription['status']),
                'cancel_at_period_end' => $stripeSubscription['cancel_at_period_end'],
                'ended_at' => $stripeSubscription['ended_at'],
                'current_period_end_at' => $stripeSubscription['current_period_end']
            ]);

            $stripeSubscriptionData = collect([
                'total' => $this->convertStripePrice($stripeSubscription['items']['data'][0]['plan']['amount']),
                'currency' => $stripeSubscription['items']['data'][0]['plan']['currency']
            ]);

            event( New SubscriptionPastDue($subscription->sharingUser, $stripeSubscriptionData));
        }

        return $this->successMethod();
    }

    /**
     * Conferma cancellazione della sottoscrizione
     *
     */

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {

        $object = $payload['data']['object'];
        $subscription = Subscription::findOrFail($object['id']);

        $subscription->update([
            'status' => SubscriptionStatus::getValue($object['status']),
            'cancel_at_period_end' => $object['cancel_at_period_end'],
            'ended_at' => $object['ended_at'],
            'current_period_end_at' => $object['current_period_end']
        ]);

        $this->applyTransition($subscription->sharingUser, 'left');
        event( New SubscriptionDeleted($subscription));

        return $this->successMethod();
    }

    protected function handleChargeRefunded(array $payload)
    {
        $object = $payload['data']['object']['refunds']['data'][0];

        $refund = Refund::where('payment_intent', $object['payment_intent'])->firstOrFail();

        $refund->update([
            'stripe_id' => $object['id'],
            'status' => RefundStripeStatus::getValue($object['status']),
            'internal_status' => RefundApplicationStatus::Approved
        ]);

        $subscription = \Stripe\Subscription::retrieve($refund->invoice->subscription_id);
        $subscription->cancel();

        return $this->successMethod();
    }

    protected function handleChargeRefundUpdated(array $payload)
    {
        logger("Dovrebbe essere invocato solo quando il rimborso fallisce da parte di stripe");

        return $this->successMethod();
    }

    protected function handleTransferCreated(array $payload)
    {

        //$object = $payload['data']['object'];

        //Invoice::where('payment_intent', $object['transfer_group'])->firstOrFail()->transfer()->create([
        //    'stripe_id' => $object['id'],
        //    'account_id' => $object['destination'],
        //    'amount' => $object['amount'],
        //    'currency' => $object['currency'],
        //]);

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
