<?php

namespace App\Http\Resources;

use App\Http\Traits\Utility;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function foo\func;

class Category extends JsonResource
{
    use Utility;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $images_archive = collect(Storage::files('archive/' . $this->str_id))->filter(function($file){
            return !Str::endsWith($file, '.DS_Store');
        })->map(function($file){
            return Storage::url($file);
        })->toArray();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'custom' => $this->custom,
            'price' => $this->price,
            //'capacity' => $this->capacity,
            'slot' => $this->freeSlot,
            'forbidden' => $this->whenLoaded('categoryForbidden', $this->custom ? false : true),
            'images_archive' => $images_archive
        ];
    }
}
