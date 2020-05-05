<?php

namespace App\Listeners;

use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use SM\Event\SMEvents;
use SM\Event\TransitionEvent;

class SharingTransitionSubscriber
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

    public function onTestTransition(TransitionEvent $event)
    {
    }

    public function onPreTransition(TransitionEvent $event)
    {

        //$sharingUser = $event->getStateMachine()->getObject();

        //switch ($event->getTransition()){
        //    case 'pay':
        //        if(!$sharingUser->subscription || $sharingUser->subscription->status !== SubscriptionStatus::active){
        //            $event->setRejected();
        //        }
        //        break;
        //}
    }

    public function onPostTransition(TransitionEvent $event)
    {
        $sharingUser = $event->getStateMachine()->getObject();
        $status = SharingStatus::getDescription($event->getConfig()['to']);

        switch ($event->getTransition()){
            case 'pay':
                // Send Mail to owner and user
                $sharingUser->sharing->owner->notify(new \App\Notifications\SharingNewMember($sharingUser->sharing, $sharingUser->user, true));
                $sharingUser->user->notify(new \App\Notifications\SharingNewMember($sharingUser->sharing, $sharingUser->user));
                break;
            default :
                $sharingUser->user->notify(new \App\Notifications\SharingUserUpdated($sharingUser->sharing, $status));
                break;
        }
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
            SMEvents::TEST_TRANSITION,
            'App\Listeners\SharingTransitionSubscriber@onTestTransition'
        );

        $event->listen(
            SMEvents::PRE_TRANSITION,
            'App\Listeners\SharingTransitionSubscriber@onPreTransition'
        );

        $event->listen(
            SMEvents::POST_TRANSITION,
            'App\Listeners\SharingTransitionSubscriber@onPostTransition'
        );

    }
}
