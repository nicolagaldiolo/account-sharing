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

        // la transazione è in entrata se ricevo i soldi, è in uscita se sto inviando del denaro
        // la variabile last4 contiene i dati della carta che invia il denaro, o il numero del conto nel caso di payout
        $incoming = $request->user()->pl_account_id == $this->transactiontable->owner->pl_account_id;

        switch ($this->transactiontable_type){
            case 'App\Invoice':
                $type = 'INVOICE';
                $direction = ($incoming) ? $incoming_label : $outcoming_label;
                $showLast4 = !$incoming;
                $user = ($incoming) ? $this->transactiontable->user->username : $this->transactiontable->owner->username;
                $paymentIntent = $this->transactiontable->payment_intent;
                $refundable = !$incoming;
                break;
            case 'App\Refund':
                $type = 'REFUND';
                $direction = (!$incoming) ? $incoming_label : $outcoming_label;
                $showLast4 = !$incoming;
                $user = ($incoming) ? $this->transactiontable->user->username : $this->transactiontable->owner->username;
                $paymentIntent = $this->transactiontable->payment_intent;
                $refundable = false;
                break;
            case 'App\Payout':
                $type = 'PAYOUT';
                $direction = $outcoming_label;
                $showLast4 = true;
                $user = $this->transactiontable->owner->username;
                $paymentIntent = false;
                $refundable = false;
                break;
        }

        //return parent::toArray($request);
        return [
            'type' => $type,
            'direction' => $direction,
            'user' => $user,
            $this->mergeWhen($refundable && $this->transactiontable->created_at->addDays(config('custom.day_refund_limit'))->gte(Carbon::now()->endOfDay()), [
                'refundable' => [
                    'payment_intent' => $paymentIntent,
                    'within' => $this->transactiontable->created_at->addDays(config('custom.day_refund_limit')),
                ]
            ]),
            $this->mergeWhen($showLast4, [
                'last4' => $this->transactiontable->last4,
            ]),

            $this->mergeWhen($this->transactiontable->service, [
                'title' => $this->transactiontable->service,
            ]),
            'created_at' => $this->transactiontable->created_at,
            'total' => [
                'value' => $this->transactiontable->total,
                'currency' => $this->transactiontable->currency
            ],
            //'obj' => $this->transactiontable
        ];
    }
}
