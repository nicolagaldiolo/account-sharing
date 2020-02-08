<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethod extends JsonResource
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
            'id' => $this->resource['id'],
            'card' => [
                'last4' => $this->resource['card']['last4'],
                'exp_month' => $this->resource['card']['exp_month'],
                'exp_year' => $this->resource['card']['exp_year'],
                'brand' => $this->resource['card']['brand']
            ],
        ];
    }
}
