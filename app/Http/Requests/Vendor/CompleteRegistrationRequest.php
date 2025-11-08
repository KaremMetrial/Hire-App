<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

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
            'identifier' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identifier.required' => __('validation.required', ['attribute' => 'identifier']),
            'identifier.string' => __('validation.string', ['attribute' => 'identifier']),
            'otp.required' => __('validation.required', ['attribute' => 'OTP']),
            'otp.string' => __('validation.string', ['attribute' => 'OTP']),
            'otp.size' => __('validation.size.string', ['attribute' => 'OTP', 'size' => '6']),
            'password.required' => __('validation.required', ['attribute' => 'password']),
            'password.string' => __('validation.string', ['attribute' => 'password']),
            'password.min' => __('validation.min.string', ['attribute' => 'password', 'min' => '8']),
            'password.confirmed' => __('validation.confirmed', ['attribute' => 'password']),
        ];
    }
}
