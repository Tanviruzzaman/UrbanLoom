<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackOrderRequest extends FormRequest
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
            'order_number' => ['required', 'string', 'max:50'],

            // The phone number is required on purpose. Order numbers are
            // guessable enough that number alone would let a stranger read
            // another customer's name, address and phone.
            'customer_phone' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.required' => 'Please enter your order number.',
            'customer_phone.required' => 'Please enter the mobile number you ordered with.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_number' => strtoupper(trim((string) $this->input('order_number'))),
            'customer_phone' => preg_replace('/[\s-]/', '', (string) $this->input('customer_phone')),
        ]);
    }
}
