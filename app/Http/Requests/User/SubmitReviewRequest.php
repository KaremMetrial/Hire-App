<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'cleanliness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'service_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'value_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Overall rating is required',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating cannot be more than 5',
            'cleanliness_rating.min' => 'Cleanliness rating must be at least 1',
            'cleanliness_rating.max' => 'Cleanliness rating cannot be more than 5',
            'service_rating.min' => 'Service rating must be at least 1',
            'service_rating.max' => 'Service rating cannot be more than 5',
            'value_rating.min' => 'Value rating must be at least 1',
            'value_rating.max' => 'Value rating cannot be more than 5',
            'comment.max' => 'Comment cannot exceed 1000 characters',
        ];
    }
}
