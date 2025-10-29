<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class RequestInfoRequest extends FormRequest
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
            'information_requests' => ['required', 'array', 'min:1'],
            'information_requests.*.field' => ['required', 'string', 'in:license_number,license_expiry_date,face_license_id_photo,back_license_id_photo,nationality,emergency_contact_name,emergency_contact_phone,driving_experience_years'],
            'information_requests.*.is_required' => ['boolean'],
            'information_requests.*.notes' => ['nullable', 'string', 'max:500'],
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
            'information_requests.required' => 'Information requests are required.',
            'information_requests.array' => 'Information requests must be an array.',
            'information_requests.min' => 'At least one information request is required.',
            'information_requests.*.field.required' => 'Field is required for each request.',
            'information_requests.*.field.in' => 'Invalid field specified.',
            'information_requests.*.is_required.boolean' => 'Is required must be true or false.',
            'information_requests.*.notes.max' => 'Notes may not be greater than 500 characters.',
        ];
    }
}
