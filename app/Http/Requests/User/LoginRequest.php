<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Improved phone validation: allows international formats with + and spaces
        $isPhone = preg_match('/^[\+]?[\d\s\-\(\)]+$/', $this->identifier);

        return [
            'identifier' => [
                'required',
                'string',
                'max:255',
                // If it looks like a phone number, validate as a phone.
                Rule::when($isPhone, [
                    'regex:/^[\+]?[\d\s\-\(\)]+$/',
                    Rule::exists('users', 'phone'),
                ]),
                // Otherwise, validate as an email.
                Rule::when(! $isPhone, [
//                    'email:rfc,dns',
                        'email',
                    Rule::exists('users', 'email'),
                ]),
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'identifier' => __('validation.attributes.identifier'),
            'password' => __('validation.attributes.password'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize identifier input to prevent injection attacks
        $this->merge([
            'identifier' => trim(strip_tags($this->identifier)),
        ]);
    }
}
