<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalShopRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'transport_license_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'commerical_registration_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'address' => ['nullable', 'array'],
            'address.latitude' => ['nullable', 'string'],
            'address.longitude' => ['nullable', 'string'],
            'address.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'facebook_link' => ['nullable', 'string', 'max:255'],
            'instagram_link' => ['nullable', 'string', 'max:255'],
            'whatsapp_link' => ['nullable', 'string', 'max:255'],
        ];
    }
}
