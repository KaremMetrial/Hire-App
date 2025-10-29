<?php

namespace App\Http\Requests\User;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class SubmitBookingInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $bookingId = $this->route('booking');
        $booking = Booking::find($bookingId);
        return $booking && $booking->user_id == auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bookingId = $this->route('booking');
        $booking = Booking::find($bookingId);
        $rules = [];

        if ($booking && $booking->informationRequests) {
            foreach ($booking->informationRequests->where('status', 'pending') as $request) {
                $field = $request->requested_field;
                $isRequired = $request->is_required;

                switch ($field) {
                    case 'license_number':
                        $rules[$field] = $isRequired ? 'required|string|max:50' : 'nullable|string|max:50';
                        break;
                    case 'license_expiry_date':
                        $rules[$field] = $isRequired ? 'required|date|after:today' : 'nullable|date|after:today';
                        break;
                    case 'face_license_id_photo':
                    case 'back_license_id_photo':
                        $rules[$field] = $isRequired ? 'required|image|mimes:jpeg,png,jpg|max:2048' : 'nullable|image|mimes:jpeg,png,jpg|max:2048';
                        break;
                    case 'nationality':
                        $rules[$field] = $isRequired ? 'required|string|max:100' : 'nullable|string|max:100';
                        break;
                    case 'emergency_contact_name':
                        $rules[$field] = $isRequired ? 'required|string|max:255' : 'nullable|string|max:255';
                        break;
                    case 'emergency_contact_phone':
                        $rules[$field] = $isRequired ? 'required|string|max:20' : 'nullable|string|max:20';
                        break;
                    case 'driving_experience_years':
                        $rules[$field] = $isRequired ? 'required|integer|min:0|max:50' : 'nullable|integer|min:0|max:50';
                        break;
                }
            }
        }

        $rules['additional_notes'] = 'sometimes|string|max:1000';

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_number.required' => 'License number is required.',
            'license_number.max' => 'License number may not be greater than 50 characters.',
            'license_expiry_date.required' => 'License expiry date is required.',
            'license_expiry_date.after' => 'License expiry date must be after today.',
            'nationality.required' => 'Nationality is required.',
            'emergency_contact_name.required' => 'Emergency contact name is required.',
            'emergency_contact_phone.required' => 'Emergency contact phone is required.',
            'driving_experience_years.required' => 'Driving experience years is required.',
            'driving_experience_years.integer' => 'Driving experience must be a number.',
            'driving_experience_years.min' => 'Driving experience cannot be negative.',
            'driving_experience_years.max' => 'Driving experience cannot exceed 50 years.',
            'additional_notes.max' => 'Additional notes may not be greater than 1000 characters.',
        ];
    }
}
