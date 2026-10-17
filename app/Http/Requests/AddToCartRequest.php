<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id'),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'.config('store.cart.max_quantity_per_item', 10),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Please choose a size before adding to cart.',
            'product_variant_id.exists' => 'That size is no longer available.',
            'quantity.max' => 'You can order at most :max of one size per order.',
        ];
    }
}
