<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date|after:now',
            'return_date' => 'required|date|after:pickup_date',
            'duration_type' => 'nullable|in:day,hour',

            'pickup_location_type' => 'required|in:office,custom',
            'pickup_address' => 'required_if:pickup_location_type,custom|nullable|string',

            'return_location_type' => 'required|in:office,custom',
            'return_address' => 'required_if:return_location_type,custom|nullable|string',

            'delivery_option_id' => 'nullable|exists:delivery_options,id',

            'extra_services' => 'nullable|array',
            'extra_services.*.extra_service_id' => 'required|exists:extra_services,id',
            'extra_services.*.price' => 'required|numeric|min:0',
            'extra_services.*.quantity' => 'nullable|integer|min:1',

            'insurances' => 'nullable|array',
            'insurances.*.insurance_id' => 'required|exists:insurances,id',
            'insurances.*.price' => 'required|numeric|min:0',
            'insurances.*.deposit_price' => 'required|numeric|min:0',

            'discount' => 'nullable|numeric|min:0',
            'customer_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'car_id.required' => 'يجب تحديد السيارة',
            'car_id.exists' => 'السيارة غير موجودة',
            'pickup_date.required' => 'يجب تحديد تاريخ الاستلام',
            'pickup_date.after' => 'يجب أن يكون تاريخ الاستلام في المستقبل',
            'return_date.required' => 'يجب تحديد تاريخ التسليم',
            'return_date.after' => 'يجب أن يكون تاريخ التسليم بعد تاريخ الاستلام',
            'pickup_location_type.required' => 'يجب تحديد نوع موقع الاستلام',
            'pickup_address.required_if' => 'يجب تحديد عنوان الاستلام',
            'return_location_type.required' => 'يجب تحديد نوع موقع التسليم',
            'return_address.required_if' => 'يجب تحديد عنوان التسليم',
        ];
    }
}
