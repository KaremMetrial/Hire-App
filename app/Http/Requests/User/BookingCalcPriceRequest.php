<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class BookingCalcPriceRequest extends FormRequest
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
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date|after:now',
            'return_date' => 'required|date|after:pickup_date',
            'pickup_location_type' => 'required|in:office,custom',
            'return_location_type' => 'required|in:office,custom',
            'pickup_address' => 'required_if:pickup_location_type,custom|string',
            'return_address' => 'required_if:return_location_type,custom|string',
            'extra_services' => 'sometimes|array',
            'extra_services.*.id' => 'required|exists:extra_services,id',
            'extra_services.*.quantity' => 'required|integer|min:1',
            'insurance_id' => 'sometimes|exists:insurances,id',
            'price_id' => 'sometimes|exists:car_prices,id',
        ];
    }
}
