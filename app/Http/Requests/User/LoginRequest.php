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
        // A simple check to see if the identifier looks like a phone number (all digits).
        $isPhone = preg_match('/^\d+$/', $this->identifier);

        return [
            'identifier' => [
                'required',
                'string',
                // If it looks like a phone number, validate as a phone.
                Rule::when($isPhone, [
                    'numeric',
                    Rule::exists('users', 'phone'),
                ]),
                // Otherwise, validate as an email.
                Rule::when(! $isPhone, [
                    'email',
                    Rule::exists('users', 'email'),
                ]),
            ],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function attributes(): array
    {
        return [
            'identifier' => __('validation.attributes.identifier'),
            'password' => __('validation.attributes.password'),
        ];
    }
}
