<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Invoice extends JsonResource
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
        $refund_deadline = $this->created_at->addDays(config('custom.day_refund_limit'));

        return [
            'id' => $this->id,
            'direction' => ($incoming) ? $incoming_label : $outcoming_label,
            'service' => $this->service,
            'user' => ($incoming) ? $this->user->username : $this->owner->username,
            'payment_intent' => $this->payment_intent,
            'refundable' => !$incoming && !$this->refund && $refund_deadline->gte(Carbon::now()->endOfDay()),
            'refundable_within' => $refund_deadline,
            'refunded' => $this->refundApproved,
            $this->mergeWhen(!$incoming, [
                'last4' => $this->last4,
            ]),
            'created_at' => $this->created_at,
            'total' => [
                'value' => (float) ($incoming ? $this->total_less_fee : $this->total),
                'currency' => $this->currency
            ]
        ];

    }
}
