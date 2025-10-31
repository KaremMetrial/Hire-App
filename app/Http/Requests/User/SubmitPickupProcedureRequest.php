<?php

namespace App\Http\Requests\User;

use App\Enums\CarImageTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPickupProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => 'nullable|string|max:1000',
            'images' => 'required|array|min:1|max:10',
            'images.*.image' => 'required|image|mimes:jpg,png,jpeg,webp|max:2048',
            'images.*.image_type' => ['required', 'string', Rule::in(CarImageTypeEnum::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
            'images.required' => 'يجب إضافة صور للإجراء',
            'images.min' => 'يجب إضافة صورة واحدة على الأقل',
            'images.max' => 'لا يمكن إضافة أكثر من 10 صور',
            'images.*.image.required' => 'الصورة مطلوبة',
            'images.*.image.image' => 'الملف يجب أن يكون صورة',
            'images.*.image.mimes' => 'الصورة يجب أن تكون من نوع: jpg, png, jpeg, webp',
            'images.*.image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            'images.*.image_type.required' => 'نوع الصورة مطلوب',
            'images.*.image_type.in' => 'نوع الصورة غير صحيح',
        ];
    }
}
