<?php

namespace App\Http\Resources;

use App\Enums\RenewalFrequencies;
use Illuminate\Http\Resources\Json\JsonResource;

class RenewalFrequency extends JsonResource
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
            'value' => $this->value,
            'type' => $this->type,
            'frequency' => $this->value . ' ' . RenewalFrequencies::getDescription($this->type)
        ];
    }
}
