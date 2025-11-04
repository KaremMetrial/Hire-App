<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
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
            'identifier' => 'required',
            'type' => 'required|in:user,vendor',
            'purpose' => 'nullable|in:update-vendor-info,pre_registration,forgot_password',
        ];
    }

    public function attributes(): array
    {
        return [
            'identifier' => __('validation.attributes.identifier'),
            'type' => __('validation.attributes.type'),
        ];
    }
}
