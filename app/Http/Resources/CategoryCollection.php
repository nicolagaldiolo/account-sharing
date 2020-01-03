<?php

namespace App\Http\Resources;

use App\Category;
use App\Enums\SharingVisibility;
use App\RenewalFrequency;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $embed = ($request->has('embed') ? explode(',', $request->input('embed')) : []);

        return [
            'categories' => $this->collection,
            $this->mergeWhen(in_array('renewal_frequencies', $embed), [
                'renewal_frequencies' => RenewalFrequency::all(),
            ]),
            $this->mergeWhen(in_array('sharings_visibility', $embed), [
                'sharings_visibility' => SharingVisibility::toSelectArray()
            ])
        ];
    }
}
