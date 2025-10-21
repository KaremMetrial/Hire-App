<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:vendors,email,'.$this->user()->id],
            'phone' => ['nullable', 'string', 'unique:vendors,phone,'.$this->user()->id],
            'national_id_photo' => ['nullable', 'image', 'mimes:png,jpeg,jpg'],
            'otp_code' => ['nullable', 'string', 'max:6'],
        ];
    }
}
