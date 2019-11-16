<?php

namespace App\Http\Traits;

use App\Enums\SharingStatus;
use App\Enums\SubscriptionStatus;
use App\Sharing;
use App\SharingUser;
use App\Subscription;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast\Object_;

trait SharingTrait {

    protected function getSharing(Sharing $sharing)
    {

        //$sharing->load(['category', 'activeUsers', 'activeUsersWithoutOwner']);
        $sharing->load(['category', 'activeUsersWithoutOwner']);

        $sharing->active_users = $sharing->activeUsers()->get()->each(function($user) use($sharing){
            return $user->sharing_status->subscription;
        });

        return $sharing;
    }

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

    protected function updateSubscription(Subscription $subscription, $payload)
    {


        $subscription->update([
            'status' => SubscriptionStatus::getValue($payload['status']),
            'cancel_at_period_end' => $payload['cancel_at_period_end'],
            'ended_at' => $payload['ended_at'],
            'current_period_end_at' => $payload['current_period_end']
        ]);

        //if ($subscription->cancel_at_period_end != $payload->cancel_at_period_end){
        //    $subscription->cancel_at_period_end = $payload->cancel_at_period_end;
        //    $subscription->save();
        //}
        //return $subscription;
    }

    /*public function getSharingStateMachineAttribute($item){
        $sharing = Auth::user()->sharings()->find($item->id);

        if(!is_null($sharing)){
            //logger("ENTRI QUI");
            $stateMachine = \StateMachine::get($sharing->sharing_status, 'sharing');
            return [
                'status' => [
                    'value' => $stateMachine->getState(),
                    'metadata' => $stateMachine->metadata('state'),
                ],
                'transitions' => collect([])->merge(collect($stateMachine->getPossibleTransitions())->map(function($value) use($stateMachine){
                    return [
                        'value' => $value,
                        'metadata' => $stateMachine->metadata()->transition($value)
                    ];
                }))->all(), // altrimenti non mi torna un array;
            ];
        }else{
            //logger("FALLISCO");
            return null;
        }
    }
    */
}
