<?php

namespace App\Http\Resources;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
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
        $incoming = $request->user()->id == $this->transactiontable->owner->id;

        switch ($this->transactiontable_type){
            case 'App\Invoice':

                $refund_deadline = $this->transactiontable->created_at->addDays(config('custom.day_refund_limit'));
                $refundable = !$incoming && !$this->transactiontable->refund && $refund_deadline->gte(Carbon::now()->endOfDay());

                return [
                    'id' => $this->transactiontable->id,
                    'type' => 'INVOICE',
                    'direction' => ($incoming) ? $incoming_label : $outcoming_label,
                    'service' => $this->transactiontable->service,
                    'user' => ($incoming) ? $this->transactiontable->user->username : $this->transactiontable->owner->username,
                    $this->mergeWhen($refundable, [
                        'refundable' => [
                            'payment_intent' => $this->transactiontable->payment_intent,
                            'within' => $refund_deadline,
                        ]
                    ]),
                    'refunded' => $this->transactiontable->refundApproved,
                    $this->mergeWhen(!$incoming, [
                        'last4' => $this->transactiontable->last4,
                    ]),
                    'created_at' => $this->transactiontable->created_at,
                    'total' => [
                        'value' => (float) ($incoming ? $this->transactiontable->total_less_fee : $this->transactiontable->total),
                        'currency' => $this->transactiontable->currency
                    ]
                ];

                break;
            case 'App\Refund':
                return new Refund($this->transactiontable);
                break;
            case 'App\Payout':
                return [
                    'id' => $this->transactiontable->id,
                    'type' => 'PAYOUT',
                    'direction' => $outcoming_label,
                    'user' => $this->transactiontable->owner->username,
                    'last4' => $this->transactiontable->last4,
                    'created_at' => $this->transactiontable->created_at,
                    'total' => [
                        'value' => (float) $this->transactiontable->total,
                        'currency' => $this->transactiontable->currency
                    ]
                ];
                break;
        }
    }
}
