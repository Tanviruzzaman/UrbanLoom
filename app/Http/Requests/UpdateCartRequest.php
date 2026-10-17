<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
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
            // Zero is allowed: it removes the line.
            'quantity' => [
                'required',
                'integer',
                'min:0',
                'max:'.config('store.cart.max_quantity_per_item', 10),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.max' => 'You can order at most :max of one size per order.',
        ];
    }
}
