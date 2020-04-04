<?php

namespace App\Listeners;

use App\Enums\SharingApprovationStatus;
use App\Events\SharingCreated;
use App\Events\SharingStatusUpdated;
use App\Events\SubscriptionNewMember;
use App\MyClasses\Support\Facade\Stripe;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SharingSubscriber
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

    public function onSharingCreated($event)
    {
        $sharing = $event->sharing;

        if($sharing->status === SharingApprovationStatus::Approved){
            // Create stripe plan
            Stripe::createPlan($sharing);
        }

        // Send Mail to owner
        $sharing->owner->notify(new \App\Notifications\SharingStatusUpdated($sharing));

        // Send Mail to admins
        Notification::send(User::admin()->get(), new \App\Notifications\SharingCreated($sharing));
    }

    public function onSharingStatusUpdated($event)
    {
        $sharing = $event->sharing;

        if($sharing->status === SharingApprovationStatus::Approved){
            // Create stripe plan
            Stripe::createPlan($sharing);
        }

        // Send Mail to owner
        $sharing->owner->notify(new \App\Notifications\SharingStatusUpdated($sharing));
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
            SharingCreated::class,
            'App\Listeners\SharingSubscriber@onSharingCreated'
        );

        $event->listen(
            SharingStatusUpdated::class,
            'App\Listeners\SharingSubscriber@onSharingStatusUpdated'
        );
    }
}
