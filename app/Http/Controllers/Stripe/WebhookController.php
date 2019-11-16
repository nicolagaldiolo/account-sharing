<?php

namespace App\Http\Controllers\Stripe;

use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Middleware\VerifyWebhookSignature;
use App\Http\Traits\SharingTrait;
use App\SharingUser;
use App\Subscription;
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
        if (config('stripe.webhook.secret')) {
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

        $subscription_id = $payload['data']['object']['subscription'];

        $userSharing = Subscription::where('stripe_id', $subscription_id)->firstOrFail()->sharingUser;

        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        $stateMachine = \StateMachine::get($userSharing, 'sharing');
        $transition = 'pay';

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $userSharing->save();
        }

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



    /**
     * Cambio di stato della sottoscrizione
     *
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {

        $status = $payload['data']['object']['status'];

        $subscription_id = $payload['data']['object']['id'];
        $userSharing = Subscription::where('stripe_id', $subscription_id)->firstOrFail()->sharingUser;

        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        //$stateMachine = \StateMachine::get($userSharing, 'sharing');

        $this->updateSubscription($userSharing->subscription, $payload['data']['object']);

        //if($stateMachine->getState() === SharingStatus::Joined){
            // Se la sottoscrizione è attiva
            //if($status === 'active') {



            // Altrimenti se è scaduta, nel caso in cui il pagamento sia fallito
            //}elseif ($status === 'past_due'){

            //    logger("Sottoscrizione scaduta");

            //}
        //}else{
        //    abort(403);
        //}

        return $this->successMethod();
    }

    /**
     * Conferma creazione della sottoscrizione
     *
     */
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        //logger($payload);
        /*
        $subscription = $payload['data']['object'];

        $userSharing = SharingUser::whereSharingId($subscription['metadata']['sharing_id'])
            ->whereUserId($subscription['metadata']['user_id'])
            ->firstOrFail();

        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        $stateMachine = \StateMachine::get($userSharing, 'sharing');

        //DB::transaction(function() use ($stateMachine, $userSharing, $subscription){
            if($stateMachine->can('pay')) {

                $userSharing->subscription()->create([
                    'stripe_id' => $subscription['id'],
                    'status' => SubscriptionStatus::getValue($subscription['status']),
                    'current_period_end_at' => $subscription['current_period_end']
                ]);

            }
        //});

        */


        return $this->successMethod();
    }

    /**
     * Conferma cancellazione della sottoscrizione
     *
     */

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {

        $subscription_id = $payload['data']['object']['id'];
        $userSharing = Subscription::where('stripe_id', $subscription_id)->firstOrFail()->sharingUser;

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

    /**
     * Handle customer updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */

    /*
    protected function handleCustomerUpdated(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['id'])) {
            $user->updateDefaultPaymentMethodFromStripe();
        }

        return $this->successMethod();
    }
    */

    /**
     * Handle deleted customer.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */

    /*
    protected function handleCustomerDeleted(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['id'])) {
            $user->subscriptions->each(function (Subscription $subscription) {
                $subscription->skipTrial()->markAsCancelled();
            });

            $user->forceFill([
                'stripe_id' => null,
                'trial_ends_at' => null,
                'card_brand' => null,
                'card_last_four' => null,
            ])->save();
        }

        return $this->successMethod();
    }
    */

    /**
     * Handle payment action required for invoice.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */

    /*
    protected function handleInvoicePaymentActionRequired(array $payload)
    {
        if (is_null($notification = config('cashier.payment_notification'))) {
            return $this->successMethod();
        }

        if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            if (in_array(Notifiable::class, class_uses_recursive($user))) {
                $payment = new Payment(StripePaymentIntent::retrieve(
                    $payload['data']['object']['payment_intent'],
                    $user->stripeOptions()
                ));

                $user->notify(new $notification($payment));
            }
        }

        return $this->successMethod();
    }
    */

    /**
     * Get the billable entity instance by Stripe ID.
     *
     * @param  string|null  $stripeId
     * @return \Laravel\Cashier\Billable|null
     */

    /*
    protected function getUserByStripeId($stripeId)
    {
        if ($stripeId === null) {
            return;
        }

        $model = config('cashier.model');

        return (new $model)->where('stripe_id', $stripeId)->first();
    }
    */

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
