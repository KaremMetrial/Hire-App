<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StartBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_mileage' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_mileage.required' => 'يجب تحديد قراءة العداد عند الاستلام',
            'pickup_mileage.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً',
            'pickup_mileage.min' => 'قراءة العداد يجب أن تكون صفر أو أكثر',
        ];
    }
}
