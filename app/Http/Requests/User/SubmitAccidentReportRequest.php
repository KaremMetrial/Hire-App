<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAccidentReportRequest extends FormRequest
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
            'booking_id' => 'required|exists:bookings,id',
            'accident_location' => 'required|string|max:255',
            'accident_details' => 'required|string|max:1000',
            'accident_location_coordinates' => 'nullable|array',
            'accident_location_coordinates.latitude' => 'nullable|numeric|between:-90,90',
            'accident_location_coordinates.longitude' => 'nullable|numeric|between:-180,180',
            'accident_date' => 'required|date|before_or_equal:today',
            'severity' => 'required|in:minor,moderate,major',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'image_descriptions' => 'nullable|array',
            'image_descriptions.*' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'booking_id.required' => 'Booking ID is required.',
            'booking_id.exists' => 'Invalid booking selected.',
            'accident_location.required' => 'Accident location is required.',
            'accident_details.required' => 'Accident details are required.',
            'accident_date.required' => 'Accident date is required.',
            'accident_date.before_or_equal' => 'Accident date cannot be in the future.',
            'severity.required' => 'Accident severity is required.',
            'severity.in' => 'Invalid severity level.',
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ];
    }
}
