<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Chat' => 'App\Policies\ChatPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manage-own-sharing', function($user, $sharingUser){
            $sharingUser = $sharingUser->load('sharing');
            return $user->id === $sharingUser->sharing->owner_id;
        });

        Gate::define('pay-sharing', function($user, $sharingUser){
            return $user->id === $sharingUser->user_id;
        });
    }
}
