<?php

namespace App\Http\Requests;

use App\Category;
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

        $category = Category::where('id', $this->input('category_id'))->first();
        $max_price = ($category->price > 0) ? '|max:' . $category->price : '';
        $max_capacity = ($category->capacity > 0) ? '|max:' . $category->capacity : '';

        return [
            'name'                  => 'required|max:255',
            'description'           => 'sometimes|max:750',
            'visibility'            => ['required', new EnumValue(SharingVisibility::class, false)],
            'capacity'              => 'required|numeric' . $max_capacity,
            'price'                 => 'required|numeric' . $max_price,
            'category_id'           => 'required|exists:categories,id',
            'renewal_frequency_id'  => 'required|exists:renewal_frequencies,id'
        ];
    }
}