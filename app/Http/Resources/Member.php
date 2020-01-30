<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Subscription as SubscriptionResource;
use Illuminate\Support\Facades\Auth;

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
        $subscription = $credential_status = null;

        if($this->sharing_status){
            $status = true;
            $subscription = new SubscriptionResource($this->sharing_status->subscription);
            $credential_status = $this->sharing_status->credential_status;
        }

        return [
            'id' => $this->id,
            'photo_url' => $this->photo_url,
            'username' => $this->username,
            $this->mergeWhen($status, [
                'subscription' => $subscription,
                'credential_status' => $credential_status
            ]),
        ];
    }
}
