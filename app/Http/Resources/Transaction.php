<?php

namespace App\Http\Resources;

use App\User;
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
                break;
            case 'App\Refund':
                $type = 'REFUND';
                $direction = ($incoming) ? $incoming_label : $outcoming_label;
                $showLast4 = $incoming;
                $user = ($incoming) ? $this->transactiontable->user->username : $this->transactiontable->owner->username;
                break;
            case 'App\Payout':
                $type = 'PAYOUT';
                $direction = $outcoming_label;
                $showLast4 = true;
                $user = $this->transactiontable->owner->username;
                break;
        }

        //return parent::toArray($request);
        return [
            'type' => $type,
            'direction' => $direction,
            'user' => $user,
            $this->mergeWhen($showLast4, [
                'last4' => $this->transactiontable->last4,
            ]),
            $this->mergeWhen($this->transactiontable->service, [
                'title' => $this->transactiontable->service,
            ]),
            'created_at' => $this->transactiontable->created_at,
            'total' => [
                'value' => ($this->transactiontable->total / 100),
                'currency' => $this->transactiontable->currency
            ],
            //'obj' => $this->transactiontable
        ];
    }
}
