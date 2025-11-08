<?php

namespace App\Http\Requests\Vendor;

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:vendor_pre_registrations,email|unique:vendors,email',
            'phone' => 'required|string|unique:vendor_pre_registrations,phone|unique:vendors,phone',
            'national_id_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rental_shop_name' => 'required|string|max:255',
            'rental_shop_phone' => 'required|string|unique:vendor_pre_registrations,rental_shop_phone|unique:rental_shops,phone',
            'rental_shop_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'transport_license_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'commerical_registration_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rental_shop_address' => 'required|array',
            'rental_shop_address.address' => 'required|string|max:500',
            'rental_shop_address.latitude' => 'nullable|numeric|between:-90,90',
            'rental_shop_address.longitude' => 'nullable|numeric|between:-180,180',
            'rental_shop_address.country_id' => 'required|exists:countries,id',
            'rental_shop_address.city_id' => 'nullable|exists:cities,id',
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
            'name.required' => __('validation.required', ['attribute' => 'name']),
            'email.required' => __('validation.required', ['attribute' => 'email']),
            'email.email' => __('validation.email', ['attribute' => 'email']),
            'email.unique' => __('validation.unique', ['attribute' => 'email']),
            'phone.required' => __('validation.required', ['attribute' => 'phone']),
            'phone.unique' => __('validation.unique', ['attribute' => 'phone']),
            'national_id_photo.required' => __('validation.required', ['attribute' => 'national ID photo']),
            'national_id_photo.image' => __('validation.image', ['attribute' => 'national ID photo']),
            'national_id_photo.mimes' => __('validation.mimes', ['attribute' => 'national ID photo']),
            'national_id_photo.max' => __('validation.max.file', ['attribute' => 'national ID photo', 'max' => '2MB']),
            'rental_shop_name.required' => __('validation.required', ['attribute' => 'rental shop name']),
            'rental_shop_phone.required' => __('validation.required', ['attribute' => 'rental shop phone']),
            'rental_shop_phone.unique' => __('validation.unique', ['attribute' => 'rental shop phone']),
            'rental_shop_image.required' => __('validation.required', ['attribute' => 'rental shop image']),
            'rental_shop_image.image' => __('validation.image', ['attribute' => 'rental shop image']),
            'rental_shop_image.mimes' => __('validation.mimes', ['attribute' => 'rental shop image']),
            'rental_shop_image.max' => __('validation.max.file', ['attribute' => 'rental shop image', 'max' => '2MB']),
            'transport_license_photo.required' => __('validation.required', ['attribute' => 'transport license photo']),
            'transport_license_photo.image' => __('validation.image', ['attribute' => 'transport license photo']),
            'transport_license_photo.mimes' => __('validation.mimes', ['attribute' => 'transport license photo']),
            'transport_license_photo.max' => __('validation.max.file', ['attribute' => 'transport license photo', 'max' => '2MB']),
            'commerical_registration_photo.required' => __('validation.required', ['attribute' => 'commercial registration photo']),
            'commerical_registration_photo.image' => __('validation.image', ['attribute' => 'commercial registration photo']),
            'commerical_registration_photo.mimes' => __('validation.mimes', ['attribute' => 'commercial registration photo']),
            'commerical_registration_photo.max' => __('validation.max.file', ['attribute' => 'commercial registration photo', 'max' => '2MB']),
            'rental_shop_address.required' => __('validation.required', ['attribute' => 'rental shop address']),
            'rental_shop_address.array' => __('validation.array', ['attribute' => 'rental shop address']),
            'rental_shop_address.address.required' => __('validation.required', ['attribute' => 'address']),
            'rental_shop_address.latitude.numeric' => __('validation.numeric', ['attribute' => 'latitude']),
            'rental_shop_address.latitude.between' => __('validation.between.numeric', ['attribute' => 'latitude', 'min' => '-90', 'max' => '90']),
            'rental_shop_address.longitude.numeric' => __('validation.numeric', ['attribute' => 'longitude']),
            'rental_shop_address.longitude.between' => __('validation.between.numeric', ['attribute' => 'longitude', 'min' => '-180', 'max' => '180']),
            'rental_shop_address.country_id.required' => __('validation.required', ['attribute' => 'country']),
            'rental_shop_address.country_id.exists' => __('validation.exists', ['attribute' => 'country']),
            'rental_shop_address.city_id.exists' => __('validation.exists', ['attribute' => 'city']),
        ];
    }
}
