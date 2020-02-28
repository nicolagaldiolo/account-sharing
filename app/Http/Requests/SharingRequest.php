<?php

namespace App\Http\Requests;

use App\Category;
use App\Enums\SharingVisibility;
use App\Http\Traits\Utility;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SharingRequest extends FormRequest
{
    use Utility;

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

        $category = Category::findOrFail($this->input('category_id'));
        $max_price = ($category->price > 0) ? '|max:' . $category->price : '';

        return [
            'name'                  => 'required|max:255',
            'description'           => 'required|max:750',
            'visibility'            => ['required', new EnumValue(SharingVisibility::class, false)],
            'slot'                  => 'required|numeric|max:' . ($this->getFreeSlot($category)),
            'price'                 => 'required|numeric|min:1' . $max_price,
            'category_id'           => 'required|exists:categories,id',
            'renewal_frequency_id'  => 'required|exists:renewal_frequencies,id',
            'username'              => 'sometimes',
            'password'              => 'sometimes',
            'service_igree'         => 'required|in:1',
        ];
    }
}
