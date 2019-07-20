<?php

namespace App\Http\Requests;

use App\Enums\SharingVisibility;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class SharingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'          => 'required',
            'description'   => 'sometimes',
            'visibility'    => ['required', new EnumValue(SharingVisibility::class, false)],
            'capacity'      => 'required',
            'price'         => 'required',
            'category_id'   => 'required',
        ];
    }
}