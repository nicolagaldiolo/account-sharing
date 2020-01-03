<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class Category extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'custom' => $this->custom,
            'price' => $this->price,
            'capacity' => $this->capacity,

            // I don't create a sharing if i already created one in this category
            'forbidden' => $this->whenLoaded('categoryForbidden', true)
        ];
    }
}
