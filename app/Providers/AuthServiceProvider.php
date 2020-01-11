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

        Gate::define('manage-own-sharing', function(User $user, $sharing){
            return $user->id === $sharing->owner_id;
        });

        Gate::define('can-subscribe', function($user, $sharingUser){
            // Aggiungere anche divieto di iscrizione da parte dell'owner
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
            return !$user->additional_data_needed && (is_null($category->categoryForbidden) || $category->custom);
        });

        Gate::define('manage-sharing', function(User $user, $sharing){
            return $user->id === $sharing->owner_id || $sharing->members()->get()->pluck('id')->contains($user->id);
        });

        Gate::define('update-sharing', function(User $user, $sharing, User $userToDelete){
            return ($user->id === $sharing->owner_id && $sharing->members()->get()->pluck('id')->contains($userToDelete->id)) ||
                ($user->id === $userToDelete->id && $sharing->members()->get()->pluck('id')->contains($userToDelete->id));
        });

        Gate::define('confirm-credential', function(User $user, Sharing $sharing){

            // Get the user sharing_status
            $user_sharing_status = $sharing->users()->findOrFail($user->id)->sharing_status;

            return $user->id !== $sharing->owner_id && // If i don't the owner
                $sharing->credential_updated_at->gt($user_sharing_status->credential_updated_at) && // If sharing credentials' is after the sharing_user credential_updated_at
                $sharing->members()->get()->pluck('id')->contains($user->id); // If i am an active user
        });
    }
}
