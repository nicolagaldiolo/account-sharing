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
        $obj = null;
        $type = null;
        switch ($this->transactiontable_type){
            case 'App\Invoice':
                $type = 'INVOICE';
                $obj = new Invoice($this->transactiontable);
                break;
            case 'App\Refund':
                $type = 'REFUND';
                $obj = new Refund($this->transactiontable);
                break;
            case 'App\Payout':
                $type = 'PAYOUT';
                $obj = new Payout($this->transactiontable);
                break;
        }

        return [
            'id' => $this->id,
            'type' => $type,
            'obj' => $obj
        ];
    }
}
