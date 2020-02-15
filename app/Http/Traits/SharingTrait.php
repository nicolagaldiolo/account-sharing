<?php

namespace App\Http\Traits;

use App\Category;
use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Events\SharingSubscription;
use App\MyClasses\Support\Facade\Stripe;
use App\Sharing;
use App\SharingUser;
use App\Subscription;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SharingTrait {

    protected function getSharingOwners($id = null)
    {
        $sharings = Auth::user()->sharingOwners();

        if($id) $sharings->findOrFail($id);

        return $sharings->with('users')->get()->each(function($sharing){
            $sharing['xx_sharing_by_status'] = collect(SharingStatus::getInstances())->each(function($sharingStatus) use($sharing){

                $users = $sharing->users->where('sharing_status.status', $sharingStatus->value)->each(function($user) use($sharing){
                    $sharing_obj = $user->sharings()->whereSharingId($sharing->id)->first()->sharing_status;
                    $user->possible_transitions = \StateMachine::get($sharing_obj, 'sharing')->getPossibleTransitions();
                });
                $sharingStatus->users = $users->values();
            });
        });
    }

    protected function createSubscription($user, Sharing $sharing, $transition)
    {

        return DB::transaction(function() use ($user, $sharing, $transition) {

            if(Auth::id() != $user->id) Auth::login($user); // If i run the script off-session (webhook or seed)

            $sharingStatus = $user->sharings()->find($sharing->id)->sharing_status;
            $stateMachine = \StateMachine::get($sharingStatus, 'sharing');

            $stripeSubscription = Stripe::createSubscription($sharing, $user);

            $sharingStatus->subscription()->create([
                'id' => $stripeSubscription->id,
                'status' => SubscriptionStatus::getValue($stripeSubscription->status),
                'current_period_end_at' => $stripeSubscription->current_period_end
            ]);

            if(SubscriptionStatus::getValue($stripeSubscription->status) === SubscriptionStatus::active){
                $stateMachine->apply($transition);
                $sharingStatus->save();

                logger("Utente è dentro 1");
            }

            return $stripeSubscription;


        });

    }

    protected function updateSubscription($object, $transition = null)
    {
        return DB::transaction(function() use ($object, $transition) {

            $subscription = Subscription::where('id', $object['id'])->firstOrFail();

            $subscription->update([
                'status' => SubscriptionStatus::getValue($object['status']),
                'cancel_at_period_end' => $object['cancel_at_period_end'],
                'ended_at' => $object['ended_at'],
                'current_period_end_at' => $object['current_period_end']
            ]);

            $sharingUser = $subscription->sharingUser;

            $user = User::findOrFail($sharingUser->user_id);
            if(Auth::id() != $user->id) Auth::login($user); // If i run the script off-session (webhook or seed)

            $stateMachine = $sharingUser->stateMachine();

            if ($transition && $stateMachine->can($transition)) {

                logger("******************* INIZIO TEST");

                logger($sharingUser->stateIs());
                logger($sharingUser);

                $stateMachine->apply($transition);
                $sharingUser->save();

                logger($sharingUser->stateIs());
                logger($sharingUser);
                logger(time());
                logger("Utente è dentro 2");

                logger("******************* FINE TEST");

                event(New SharingSubscription($sharingUser));
            };

            return $subscription;
        });
    }

    public function payInvoice($subscription)
    {
        $stripeSubscription = \Stripe\Subscription::retrieve($subscription->id);

        if($stripeSubscription->status === 'incomplete' || $stripeSubscription->status === 'past_due') {
            $invoice = \Stripe\Invoice::retrieve(['id' => $stripeSubscription->latest_invoice]);
            try {
                $invoice->pay();
            }catch (\Exception $e){}
        }

        $stripeSubscription = \Stripe\Subscription::retrieve([
            'id' => $subscription->id,
            'expand' => [
                'latest_invoice.payment_intent'
            ]
        ]);

        return $stripeSubscription;
    }
}
