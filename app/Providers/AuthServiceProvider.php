<?php

namespace App\Providers;

use App\Category;
use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Invoice;
use App\Sharing;
use App\SharingUser;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
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

        Gate::define('can-subscribe', function($user, $sharingUser){
            return $user->id === $sharingUser->user_id && $sharingUser->status === SharingStatus::Approved;
        });

        Gate::define('can-restore', function($user, $sharingUser){
            return $user->id === $sharingUser->user_id && ($sharingUser->subscription && $sharingUser->subscription->status === SubscriptionStatus::getValue('past_due'));
        });

        Gate::define('left-subscription', function (User $user, SharingUser $sharingUser){

            if($sharingUser->subscription && $sharingUser->subscription->id){
                $subscription = \Stripe\Subscription::retrieve($sharingUser->subscription->id);
                return $subscription->status === 'canceled';
            }else{
                return false;
            }
        });

        Gate::define('create-sharing', function(User $user, Category $category){
            return !$user->additional_data_needed &&
                (!$user->sharingOwners()->get()->pluck('category_id')->contains($category->id) || $category->customizable);
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
