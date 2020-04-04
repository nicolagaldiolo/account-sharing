<?php

namespace App\Listeners;

use App\Events\RefundRequest;
use App\Events\RefundResponse;
use App\Notifications\RefundNewRequest;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RefundSubscriber
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

    public function onRefundRequest($event)
    {
        $refund = $event->refund;

        // Send mail to user
        $refund->user->notify( new RefundNewRequest($refund, 'USER'));

        // Send mail to owner
        $refund->owner->notify( new RefundNewRequest($refund, 'OWNER'));

        // Send mail to admins
        Notification::send(User::admin()->get(), new RefundNewRequest($refund, 'ADMIN'));
    }

    public function onRefundResponse($event)
    {
        $refund = $event->refund;

        // Send mail to owner
        $refund->owner->notify( new \App\Notifications\RefundResponse($refund, $event->action, 'OWNER'));

        // Send mail to user
        $refund->user->notify( new \App\Notifications\RefundResponse($refund, $event->action, 'USER'));

        // Send mail to admins
        Notification::send(User::admin()->get(), new \App\Notifications\RefundResponse($refund, $event->action, 'ADMIN'));
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
            RefundRequest::class,
            'App\Listeners\RefundSubscriber@onRefundRequest'
        );

        $event->listen(
            RefundResponse::class,
            'App\Listeners\RefundSubscriber@onRefundResponse'
        );
    }
}
