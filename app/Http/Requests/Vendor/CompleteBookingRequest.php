<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CompleteBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_mileage' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'return_mileage.required' => 'يجب تحديد قراءة العداد عند التسليم',
            'return_mileage.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً',
            'return_mileage.min' => 'قراءة العداد يجب أن تكون صفر أو أكثر',
        ];
    }
}
