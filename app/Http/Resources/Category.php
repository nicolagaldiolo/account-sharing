<?php

namespace App\Http\Resources;

use App\Http\Traits\UtilityTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class Category extends JsonResource
{
    use UtilityTrait;

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
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'custom' => $this->custom,
            'price' => $this->price,
            //'capacity' => $this->capacity,
            'slot' => $this->slot,
            'forbidden' => $this->whenLoaded('categoryForbidden', $this->custom ? false : true)
        ];
    }
}
