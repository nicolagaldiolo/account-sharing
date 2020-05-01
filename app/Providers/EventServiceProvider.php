<?php

namespace App\Providers;

use App\Listeners\CredentialSubscriber;
use App\Listeners\RefundSubscriber;
use App\Listeners\SharingSubscriber;
use App\Listeners\SharingTransitionSubscriber;
use App\Listeners\SubscriptionSubscriber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ]
    ];

    protected $subscribe = [
        CredentialSubscriber::class,
        RefundSubscriber::class,
        SharingSubscriber::class,
        SharingTransitionSubscriber::class,
        SubscriptionSubscriber::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
