<?php

namespace App\Listeners;

use App\Events\CredentialConfirmed;
use App\Events\CredentialUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class CredentialSubscriber
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

    public function onCredentialUpdated($event)
    {
        $sharing = $event->sharing;
        //logger($sharing);
        Notification::send($sharing->activeUsersWithoutOwner, new \App\Notifications\CredentialUpdated($sharing));
    }

    public function onCredentialConfirmed($event)
    {
        $user = $event->user;
        $sharing = $event->sharing;

        //logger("Credenziali Confermate");
        $sharing->owner->notify(new \App\Notifications\CredentialConfirmed($user, $sharing));

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
            CredentialUpdated::class,
            'App\Listeners\CredentialSubscriber@onCredentialUpdated'
        );

        $event->listen(
            CredentialConfirmed::class,
            'App\Listeners\CredentialSubscriber@onCredentialConfirmed'
        );
    }
}
