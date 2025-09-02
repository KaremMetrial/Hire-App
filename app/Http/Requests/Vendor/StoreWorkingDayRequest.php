<?php

    namespace App\Http\Requests\Vendor;

    use App\Enums\DayOfWeekEnum;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class StoreWorkingDayRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         */
        public function authorize(): bool
        {
        // Ensure the authenticated vendor is a manager of the rental shop.
        return $this->user('vendor')->rentalShops()
            ->where('rental_shops.id', $this->rental_shop_id)
            ->wherePivot('role', 'manager')
            ->exists();
        }

        /**
         * Get the validation rules that apply to the request.
         *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
         */
        public function rules(): array
        {
            return [
            'rental_shop_id' => 'required|exists:rental_shops,id',
                'day_of_week' => [
                    'required',
                Rule::enum(DayOfWeekEnum::class),
                Rule::unique('working_days')->where('rental_shop_id', $this->rental_shop_id),
                ],
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            ];
        }

        public function attributes(): array
        {
            return [
                'day_of_week' => __('validation.attributes.day_of_week'),
                'open_time' => __('validation.attributes.open_time'),
                'close_time' => __('validation.attributes.close_time'),
                'rental_shop_id' => __('validation.attributes.rental_shop_id'),
            ];
        }
    }
