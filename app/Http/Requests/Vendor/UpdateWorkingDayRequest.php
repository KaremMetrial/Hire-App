<?php

namespace App\Http\Requests\Vendor;

use App\Enums\DayOfWeekEnum;
use App\Models\WorkingDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkingDayRequest extends FormRequest
{
    /**
     * The working day instance being updated.
     *
     * @var \App\Models\WorkingDay|null
     */
    protected ?WorkingDay $workingDay = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Find the working day record from the route parameter.
        $this->workingDay = WorkingDay::find($this->route('id'));

        // If the record doesn't exist, authorization fails.
        if (!$this->workingDay) {
            return false;
        }
        // Ensure the authenticated vendor is a manager of the associated rental shop.
        return $this->user('vendor')->rentalShops()
            ->where('rental_shops.id', $this->workingDay->rental_shop_id)
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
            'day_of_week' => [
                'sometimes', // Use 'sometimes' for optional fields in an update.
                'required',
                Rule::enum(DayOfWeekEnum::class),
                Rule::unique('working_days')
                    ->where('rental_shop_id', $this->workingDay->rental_shop_id)
                    ->ignore($this->workingDay->id),
            ],
            'open_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],
            'close_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
                // Custom rule to correctly validate against open_time, whether it's
                // being updated in the same request or fetched from the database.
                function ($attribute, $value, $fail) {
                    $openTime = $this->input('open_time', $this->workingDay->open_time->format('H:i'));
                    if (strtotime($value) <= strtotime($openTime)) {
                        $fail(__('validation.after', ['attribute' => __('validation.attributes.close_time'), 'date' => __('validation.attributes.open_time')]));
                    }
                },
            ],
        ];
    }
    public function attributes(): array
    {
        return [
            'day_of_week' => __('validation.attributes.day_of_week'),
            'open_time' => __('validation.attributes.open_time'),
            'close_time' => __('validation.attributes.close_time'),
        ];
    }
}
