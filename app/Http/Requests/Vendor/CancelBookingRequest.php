<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'يجب تحديد سبب الإلغاء',
            'cancellation_reason.max' => 'سبب الإلغاء يجب ألا يتجاوز 500 حرف',
        ];
    }
}
