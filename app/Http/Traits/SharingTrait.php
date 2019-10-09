<?php

namespace App\Http\Traits;

use App\Enums\SharingStatus;
use App\Sharing;
use Illuminate\Support\Facades\Auth;

trait SharingTrait {

    protected function getSharing(Sharing $sharing)
    {

        $sharing->load(['category', 'activeUsers', 'activeUsersWithoutOwner']);

        $sharing->sharing_state_machine = $this->getSharingStateMachineAttribute($sharing);

        /*$sharing->active_users = $sharing->activeUsers()->get()->each(function($user) use($sharing){

            if(Auth::id() === $sharing->owner->id || Auth::id() == $user->id ) {
                $sharing_status = $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status;

                $state_history = $sharing_status->stateHistory()->whereTo(3)->latest()->first();
                if($state_history) $user->joiner_since = $state_history->created_at;

                $renewal = $sharing_status->renewals()->orderBy('expires_at', 'desc')->first();
                if ($renewal) {
                    $user->renewalInfo = [
                        'renewal_status' => $renewal->status,
                        'renewal_date' => $renewal->expires_at,
                        'refund_day_limit' => $renewal->expires_at->subDays(config('custom.day_refund_limit'))
                    ];
                }
            }

            return $user;
        });
        */
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

    public function getSharingStateMachineAttribute($item){
        $sharing = Auth::user()->sharings()->find($item->id);

        if(!is_null($sharing)){
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
            return null;
        }
    }
}
