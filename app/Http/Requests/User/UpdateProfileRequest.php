<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $user = $this->user();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255', 'unique:users,phone,' . $user->id],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'birthday' => ['sometimes', 'date'],
            'face_license_id_photo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            'back_license_id_photo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
        ];
    }

    /*
     * Custom Attributes Name for Validation messages
     */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.user.name'),
            'email' => __('validation.attributes.user.email'),
            'phone' => __('validation.attributes.user.phone'),
            'birthday' => __('validation.attributes.user.birthday'),
            'face_license_id_photo' => __('validation.attributes.user.face_license_id_photo'),
            'back_license_id_photo' => __('validation.attributes.user.back_license_id_photo'),
            'avatar' => __('validation.attributes.user.avatar'),
        ];
    }
}
