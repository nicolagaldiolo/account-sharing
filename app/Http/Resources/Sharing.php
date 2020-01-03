<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Sharing extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'image' => $this->image,
            'username' => $this->username,
            'password' => $this->password,
            'created_at' => $this->created_at,
            'category' => $this->category,
            'owner' => $this->owner
        ];
    }
}
