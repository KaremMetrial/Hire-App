<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CompleteRegistrationRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:5'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    /**
     * Custom Attributes Name for Validation messages
     */
    public function attributes(): array
    {
        return [
            'identifier' => __('validation.attributes.identifier'),
            'otp' => __('validation.attributes.otp'),
            'password' => __('validation.attributes.user.password'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'otp.size' => __('validation.attributes.otp_must_be_5_digits'),
            'password.confirmed' => __('validation.attributes.password_confirmation_mismatch'),
        ];
    }
}
