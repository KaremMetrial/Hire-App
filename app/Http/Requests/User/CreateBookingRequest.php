<?php

namespace App\Http\Requests\User;

use App\Enums\DeliveryOptionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
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
            'car_id' => ['required', 'exists:cars,id'],
            'price_id' => ['sometimes', 'exists:car_prices,id'],
            'pickup_date' => [
                'required',
                'date',
                'after:now',
                'before_or_equal:'.now()->addYear()->toDateTimeString(),
            ],
            'return_date' => [
                'required',
                'date',
                'after:pickup_date',
                'before_or_equal:'.now()->addYear()->toDateTimeString(),
            ],
            'pickup_location_type' => ['required', 'string', Rule::in(DeliveryOptionTypeEnum::values())],
            'return_location_type' => ['required', 'string', Rule::in(DeliveryOptionTypeEnum::values())],
            'pickup_address' => ['required_if:pickup_location_type,custom', 'string', 'max:255'],
            'return_address' => ['required_if:return_location_type,custom', 'string', 'max:255'],
            'extra_services' => ['sometimes', 'array'],
            'extra_services.*.id' => ['required', 'exists:extra_services,id'],
            'extra_services.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'insurance_id' => ['sometimes', 'exists:insurances,id'],
            'customer_notes' => ['sometimes', 'string', 'max:1000'],
            'documents' => ['sometimes', 'array', 'max:10'],
            'documents.*.document_id' => ['required', 'exists:documents,id'],
            'documents.*.file' => [
                'required_without:documents.*.value',
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
                'max:5120', // 5MB
            ],
            'documents.*.value' => ['required_without:documents.*.file', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->pickup_date && $this->return_date) {
                $pickup = \Carbon\Carbon::parse($this->pickup_date);
                $return = \Carbon\Carbon::parse($this->return_date);

                if ($pickup->diffInDays($return) > 365) {
                    $validator->errors()->add('return_date', 'The rental period cannot exceed one year.');
                }
            }

            // Validate that price_id belongs to the selected car
            if ($this->price_id && $this->car_id) {
                $priceExists = \App\Models\CarPrice::where('id', $this->price_id)
                    ->where('car_id', $this->car_id)
                    ->where('is_active', true)
                    ->exists();

                if (!$priceExists) {
                    $validator->errors()->add('price_id', 'The selected price is not valid for this car.');
                }
            }
        });
    }
}
