<?php

namespace App\Http\Resources;

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

        $incoming_label = 'INCOMING';
        $outcoming_label = 'OUTCOMING';
        $incoming = $request->user()->id == $this->owner->id;

        return [
            'id' => $this->id,
            'direction' => (!$incoming) ? $incoming_label : $outcoming_label,
            'service' => $this->service,
            'user' => ($incoming) ? $this->user->username : $this->owner->username,
            'payment_intent' => $this->payment_intent,
            'status' => $this->internal_status,
            $this->mergeWhen(!$incoming, [
                'last4' => $this->last4,
            ]),
            'created_at' => $this->created_at,
            'total' => [
                'value' => (float) ($incoming ? $this->total_less_fee : $this->total),
                'currency' => $this->currency
            ],
        ];
    }
}
