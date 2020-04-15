<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class Refund extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->internal_status,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'total' => [
                'value' => (float) $this->total,
                'currency' => $this->currency
            ],
            'sharing' => [
                'id' => $this->invoice->subscription->sharingUser->sharing->id,
                'name' => $this->invoice->subscription->sharingUser->sharing->name,
                'description' => $this->invoice->subscription->sharingUser->sharing->description,
                'image' => $this->invoice->subscription->sharingUser->sharing->publicImage,
                'category_id' => $this->invoice->subscription->sharingUser->sharing->category_id
            ],
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'country' => $this->user->country,
                'photo_url' => $this->user->photo_url
            ]
        ];
    }
}


