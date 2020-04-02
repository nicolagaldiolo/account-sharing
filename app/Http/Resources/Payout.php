<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Payout extends JsonResource
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
            'direction' => 'OUTCOMING',
            'user' => $this->owner->username,
            'last4' => $this->last4,
            'created_at' => $this->created_at,
            'total' => [
                'value' => (float) $this->total,
                'currency' => $this->currency
            ]
        ];
    }
}
