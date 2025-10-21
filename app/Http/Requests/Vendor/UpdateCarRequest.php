<?php

namespace App\Http\Requests\Vendor;

use App\Enums\CarImageTypeEnum;
use App\Enums\CarPriceDurationTypeEnum;
use App\Enums\DeliveryOptionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_of_manufacture' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
            'license_plate' => ['nullable', 'string'],
            'num_of_seat' => ['nullable', 'integer'],
            'kilometers' => ['nullable', 'integer'],
            'model_id' => ['nullable', 'exists:models,id'],
            'fuel_id' => ['nullable', 'exists:fuels,id'],
            'transmission_id' => ['nullable', 'exists:transmissions,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'rental_shop_id' => ['nullable', 'exists:rental_shops,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'rental_shop_rule' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],

            // Images
            'images' => ['nullable', 'array'],
            'images.*.image' => ['nullable', 'image', 'mimes:jpg,png,jpeg,webp', 'max:2048'],
            'images.*.image_name' => ['nullable', 'string', Rule::in(CarImageTypeEnum::values())],

            // Prices
            'prices' => ['nullable', 'array'],
            'prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.duration_type' => ['nullable', 'string', Rule::in(CarPriceDurationTypeEnum::values())],
            'prices.*.is_active' => ['nullable', 'boolean'],

            // Mileage
            'mileages' => ['nullable', 'array'],
            'mileages.limit_km_per_day' => ['nullable', 'numeric', 'min:0'],
            'mileages.limit_km_per_hour' => ['nullable', 'numeric', 'min:0'],
            'mileages.extra_fee' => ['nullable', 'numeric', 'min:0'],

            // Availabilities
            'availabilities' => ['nullable', 'array'],
            'availabilities.*.is_available' => ['nullable', 'boolean'],
            'availabilities.*.unavailable_from' => ['nullable', 'date'],
            'availabilities.*.unavailable_to' => ['nullable', 'date', 'after:availabilities.*.unavailable_from'],
            'availabilities.*.reason' => ['nullable', 'string'],

            // Insurance
            'insurances' => ['nullable', 'array'],
            'insurances.*.insurance_id' => ['nullable', 'exists:insurances,id'],

            // Extra Service
            'extra_services' => ['nullable', 'array'],
            'extra_services.*.extra_service_id' => ['nullable', 'exists:extra_services,id'],
            'extra_services.*.price' => ['nullable', 'numeric', 'min:0'],

            // Custom Extra Service
            'custom_extra_services' => ['nullable', 'array'],
            'custom_extra_services.*.name' => ['nullable', 'string'],
            'custom_extra_services.*.description' => ['nullable', 'string'],
            'custom_extra_services.*.price' => ['nullable', 'numeric', 'min:0'],

            // Delivery Options
            'delivery_options' => ['nullable', 'array'],
            'delivery_options.*.type' => ['nullable', 'string', Rule::in(DeliveryOptionTypeEnum::values())],
            'delivery_options.*.price' => ['nullable', 'numeric', 'min:0'],
            'delivery_options.*.is_active' => ['nullable', 'boolean'],
            'delivery_options.*.is_default' => ['nullable', 'boolean'],
        ];
    }
}
