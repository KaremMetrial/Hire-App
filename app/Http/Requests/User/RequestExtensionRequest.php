<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RequestExtensionRequest extends FormRequest
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
            'requested_return_date' => 'required|date|after:now',
            'reason' => 'required|string|max:500',
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
            'requested_return_date.required' => 'The requested return date is required.',
            'requested_return_date.date' => 'The requested return date must be a valid date.',
            'requested_return_date.after' => 'The requested return date must be after the current date.',
            'reason.required' => 'The reason for extension is required.',
            'reason.string' => 'The reason must be a string.',
            'reason.max' => 'The reason may not be greater than 500 characters.',
        ];
    }
}
