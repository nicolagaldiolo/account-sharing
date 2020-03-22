<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Balance extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $pending = collect($this->resource['pending']);
        $available = collect($this->resource['available']);

        return [
            'pending' => (float)($pending->first() ? $pending->first()->total : 0),
            'available' => (float)($available->first() ? $available->first()->total : 0)
        ];
    }
}
