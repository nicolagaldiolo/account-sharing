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
        $recipient = $event->recipient;

        Notification::send($sharing->members, new \App\Notifications\CredentialUpdated($sharing, $recipient));
    }

    public function onCredentialConfirmed($event)
    {
        $user = $event->user;
        $sharing = $event->sharing;
        $action = $event->action;

        $sharing->owner->notify(new \App\Notifications\CredentialConfirmed($user, $sharing, $action));


        // I must advide only other not me, i can't use the toOthers() methods because is only for broadcast class
        // https://laravel.com/docs/5.7/broadcasting#only-to-others
        // broadcast(new ShippingStatusUpdated($update))->toOthers();

        $onlyOtherUser = $sharing->members->filter(function($value) use($user){
            return $value->id !== $user->id;
        });
        Notification::send($onlyOtherUser, new \App\Notifications\CredentialConfirmed($user, $sharing, $action));

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
