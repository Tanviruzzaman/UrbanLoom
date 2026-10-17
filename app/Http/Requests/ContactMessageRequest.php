<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^(?:\+?880|0)1[3-9]\d{8}$/'],
            'order_number' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],

            // Honeypot: a real person never fills a hidden field.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Bangladeshi mobile number, e.g. 01712345678.',
            'message.min' => 'Please give us a little more detail (at least 10 characters).',
            'website.prohibited' => 'Something went wrong. Please try again.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s-]/', '', (string) $this->input('phone')),
            ]);
        }
    }
}
