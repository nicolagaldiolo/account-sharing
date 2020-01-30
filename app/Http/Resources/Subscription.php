<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Subscription extends JsonResource
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
            "status" => $this->status,
            "cancel_at_period_end" => $this->cancel_at_period_end,
            "current_period_end_at" => $this->current_period_end_at,
            "created_at" => $this->created_at,
            "refund_day_limit" => $this->created_at->addDays(config('custom.day_refund_limit'))
        ];
    }
}
