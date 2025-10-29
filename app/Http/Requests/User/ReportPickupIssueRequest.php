<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ReportPickupIssueRequest extends FormRequest
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
            'problem_details' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'problem_details.required' => 'Problem details are required',
            'problem_details.string' => 'Problem details must be a string',
            'problem_details.max' => 'Problem details cannot exceed 1000 characters',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Image must be of type: jpeg, png, jpg, gif',
            'image.max' => 'Image size cannot exceed 2MB',
        ];
    }
}
