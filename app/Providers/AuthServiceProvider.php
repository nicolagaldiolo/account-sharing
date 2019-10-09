<?php

namespace App\Providers;

use App\Sharing;
use App\User;
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
            return $user->id === $sharingUser->sharing->owner->id;
        });

        Gate::define('pay-sharing', function($user, $sharingUser){
            return $user->id === $sharingUser->user_id;
        });

        Gate::define('manage-sharing', function(User $user, Sharing $sharing){
            return $user->id === $sharing->owner->id || $sharing->activeUsers()->get()->pluck('id')->contains($user->id);
        });

        Gate::define('confirm-credential', function(User $user, Sharing $sharing){
            return $user->id !== $sharing->owner->id && // se non sono owner
                !$sharing->users()->findOrFail($user->id)->sharing_status->credential_updated_at && // se non ho le credenziali confermate
                $sharing->activeUsers()->get()->pluck('id')->contains($user->id); // se sono un utente attivo
        });
    }
}
