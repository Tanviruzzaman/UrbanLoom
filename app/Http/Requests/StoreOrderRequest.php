<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],

            // Bangladeshi mobile numbers: 01XXXXXXXXX, optionally +880 / 880 prefixed.
            'customer_phone' => ['required', 'string', 'regex:/^(?:\+?880|0)1[3-9]\d{8}$/'],

            'customer_email' => ['nullable', 'email:rfc', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Please enter your name.',
            'customer_phone.required' => 'Please enter your mobile number — we call to confirm every order.',
            'customer_phone.regex' => 'Enter a valid Bangladeshi mobile number, e.g. 01712345678.',
            'shipping_address.required' => 'Please enter your full delivery address.',
            'city.required' => 'Please choose your city.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_phone' => preg_replace('/[\s-]/', '', (string) $this->input('customer_phone')),
        ]);
    }
}
