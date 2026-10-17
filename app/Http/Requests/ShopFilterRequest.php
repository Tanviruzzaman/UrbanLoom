<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopFilterRequest extends FormRequest
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
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'sort' => ['nullable', 'string', 'in:latest,price_asc,price_desc'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.exists' => 'That category does not exist.',
        ];
    }
}
