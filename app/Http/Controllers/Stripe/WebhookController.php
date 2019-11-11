<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Middleware\VerifyWebhookSignature;
use App\SharingUser;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        logger("Eccomi");
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


    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $subscription_id = $payload['data']['object']['subscription'];
        $userSharing = SharingUser::where('stripe_subscription_id', $subscription_id)->firstOrFail();

        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        $stateMachine = \StateMachine::get($userSharing, 'sharing');
        $transition = 'pay';

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $userSharing->save();
        }

        return $this->successMethod();
    }



    /**
     * Handle customer subscription updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        logger($payload);

        $status = $payload['data']['object']['status'];

        if($status === 'active'){
            $subscription_id = $payload['data']['object']['id'];
            $userSharing = SharingUser::where('stripe_subscription_id', $subscription_id)->firstOrFail();

            $transition = ($payload['data']['object']['cancel_at_period_end']) ? 'leaving' : 'back_to_join';

            $user = User::findOrFail($userSharing->user_id);
            Auth::login($user);

            $stateMachine = \StateMachine::get($userSharing, 'sharing');
            if($stateMachine->can($transition)) {
                $stateMachine->apply($transition);
                $userSharing->save();
            }
        }elseif ($status === 'past_due'){
            logger("Sottoscrizione scaduta");

        }

        return $this->successMethod();
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        $subscription_id = $payload['data']['object']['id'];
        $userSharing = SharingUser::where('stripe_subscription_id', $subscription_id)->firstOrFail();

        $stateMachine = \StateMachine::get($userSharing, 'sharing');

        $transition = 'left';
        $user = User::findOrFail($userSharing->user_id);
        Auth::login($user);

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $userSharing->save();
        }

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
