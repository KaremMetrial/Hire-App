<?php

namespace App\Http\Requests\User;

use App\Models\Vendor;
use App\Rules\ValidOtpRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $otpService = app(\App\Services\OtpService::class);

        return [
            'otp_data' => ['required', 'array'],
            'otp_data.identifier' => ['required', 'string'],
            'otp_data.otp' => ['required', 'string'],
            'otp_data' => [new ValidOtpRule($otpService, 'user')],

            // Vendor Data
            'user' => ['required', 'array'],
            'user.name' => ['required', 'string', 'max:255'],
            'user.country_id' => ['required', 'integer', 'exists:countries,id'],
            'user.phone' => ['required', 'string', 'max:255', 'unique:users,phone'],
            'user.email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'user.birthday' => ['required', 'date'],
            'user.password' => ['required', 'string', 'confirmed', 'min:8', 'max:255'],
            'user.face_license_id_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            'user.back_license_id_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
        ];
    }

    /*
     * Custom Attributes Name for Validation messages
     */
    public function attributes(): array
    {
        return [
            'otp_data' => __('validation.attributes.otp_code'),

            'user.name' => __('validation.attributes.user.name'),
            'user.email' => __('validation.attributes.user.email'),
            'user.phone' => __('validation.attributes.user.phone'),
            'user.password' => __('validation.attributes.user.password'),
            'user.national_id_photo' => __('validation.attributes.user.national_id_photo'),
        ];
    }
}
