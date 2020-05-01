<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Events\SubscriptionDeleted;
use App\Events\SubscriptionPastDue;
use App\User;
use Illuminate\Support\Facades\Notification;

class SubscriptionSubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function onPaymentSucceeded($event)
    {
        $invoice = $event->invoice;

        // Send mail to user
        $invoice->user->notify( new \App\Notifications\PaymentSucceeded($invoice, 'USER'));

        // Send mail to owner
        $invoice->owner->notify( new \App\Notifications\PaymentSucceeded($invoice, 'OWNER'));

        // Send mail to admins
        Notification::send(User::admin()->get(), new \App\Notifications\PaymentSucceeded($invoice, 'ADMIN'));
    }

    public function onSubscriptionPastDue($event)
    {
        $event->sharingUser->user->notify( new \App\Notifications\SubscriptionPastDue($event->sharingUser->sharing, $event->stripeSubscription));
    }

    public function onSubscriptionDeleted($event)
    {
        logger('$$$$$$$ SOTTOSCRIZIONE CANCELLATA');
        logger("Avvisare della cancellazione sia admin che user, avvertire admin di cambiare le password");
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
    }

    public function subscribe($event)
    {
        $event->listen(
            PaymentSucceeded::class,
            'App\Listeners\SubscriptionSubscriber@onPaymentSucceeded'
        );

        $event->listen(
            SubscriptionPastDue::class,
            'App\Listeners\SubscriptionSubscriber@onSubscriptionPastDue'
        );

        $event->listen(
            SubscriptionDeleted::class,
            'App\Listeners\SubscriptionSubscriber@onSubscriptionDeleted'
        );
    }
}
