<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class PreRegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'phone' => ['required', 'string', 'max:255', 'unique:users,phone', 'unique:user_pre_registrations,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:user_pre_registrations,email'],
            'birthday' => ['required', 'date', 'before:today'],
            'face_license_id_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            'back_license_id_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    /**
     * Custom Attributes Name for Validation messages
     */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.user.name'),
            'country_id' => __('validation.attributes.user.country_id'),
            'phone' => __('validation.attributes.user.phone'),
            'email' => __('validation.attributes.user.email'),
            'birthday' => __('validation.attributes.user.birthday'),
            'face_license_id_photo' => __('validation.attributes.user.face_license_id_photo'),
            'back_license_id_photo' => __('validation.attributes.user.back_license_id_photo'),
            'avatar' => __('validation.attributes.user.avatar'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'birthday.before' => __('validation.attributes.user.birthday_must_be_past'),
            'phone.unique' => __('validation.attributes.user.phone_already_exists'),
            'email.unique' => __('validation.attributes.user.email_already_exists'),
        ];
    }
}
