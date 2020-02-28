<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Subscription as SubscriptionResource;

class Member extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $status = false;
        $subscription = $credential_status = $userStatus = $transitions = null;

        if($this->sharing_status){
            $status = true;
            $subscription = new SubscriptionResource($this->sharing_status->subscription);
            $credential_status = $this->sharing_status->credential_status;
            $userStatus = $this->sharing_status->status;
            $transitions = $this->sharing_status->stateMachine()->getPossibleTransitions();
        }

        return [
            'id' => $this->id,
            'photo_url' => $this->photo_url,
            'username' => $this->username,
            'created_at' => $this->created_at,
            $this->mergeWhen($status, [
                'subscription' => $subscription,
                'credential_status' => $credential_status,
                'user_status' => $userStatus,
                'transitions' => $transitions
            ])
        ];
    }
}
