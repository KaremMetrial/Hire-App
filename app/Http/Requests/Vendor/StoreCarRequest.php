<?php

    namespace App\Http\Requests\Vendor
    ;

    use App\Enums\CarImageTypeEnum;
    use App\Enums\CarPriceDurationTypeEnum;
    use App\Enums\DeliveryOptionTypeEnum;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class StoreCarRequest extends FormRequest
    {
        public function authorize(): bool
        {
            return true;
        }

        public function rules(): array
        {
            return [
                'year_of_manufacture' => ['required', 'string'],
                'num_of_seat' => ['required', 'integer'],
                'kilometers' => ['required', 'integer'],
                'model_id' => ['required', 'exists:models,id'],
                'fuel_id' => ['required', 'exists:fuels,id'],
                'transmission_id' => ['required', 'exists:transmissions,id'],
                'category_id' => ['required', 'exists:categories,id'],
                'rental_shop_id' => ['required', 'exists:rental_shops,id'],
                'city_id' => ['required', 'exists:cities,id'],
                'rental_shop_rule' => ['required', 'string'],

                // Images
                'images' => ['required', 'array'],
                'images.*.image' => ['required', 'image', 'mimes:jpg,png,jpeg,webp', 'max:2048'],
                'images.*.image_name' => ['required', 'string', Rule::in(CarImageTypeEnum::values())],

                // Prices
                'prices' => ['required', 'array'],
                'prices.*.price' => ['required', 'numeric', 'min:0'],
                'prices.*.duration_type' => ['required', 'string', Rule::in(CarPriceDurationTypeEnum::values())],
                'prices.*.is_active' => ['required', 'boolean'],

                // Mileage
                'mileages' => ['required', 'array'],
                'mileages.limit_km_per_day' => ['required', 'numeric', 'min:0'],
                'mileages.limit_km_per_hour' => ['required', 'numeric', 'min:0'],
                'mileages.extra_fee' => ['required', 'numeric', 'min:0'],

                // Availabilities
                'availabilities' => ['nullable', 'array'],
                'availabilities.*.is_available' => ['required', 'boolean'],
                'availabilities.*.unavailable_from' => [
                    'date',
                    'required_if:availabilities.*.is_available,false',
                ],
                'availabilities.*.unavailable_to' => [
                    'date',
                    'required_if:availabilities.*.is_available,false',
                    'after:availabilities.*.unavailable_from',
                ],
                'availabilities.*.reason' => ['nullable', 'string'],

                // Insurance
                'insurances' => ['nullable', 'array'],
                'insurances.*.insurance_id' => ['required', 'exists:insurances,id'],

                // Extra Service
                'extra_services' => ['nullable', 'array'],
                'extra_services.*.extra_service_id' => ['required', 'exists:extra_services,id'],
                'extra_services.*.price' => ['required', 'numeric', 'min:0'],

                // Custom Extra Service
                'custom_extra_services' => ['nullable', 'array'],
                'custom_extra_services.*.name' => ['required_with:custom_extra_services.*.price', 'string'],
                'custom_extra_services.*.description' => ['nullable', 'string'],
                'custom_extra_services.*.price' => ['required_with:custom_extra_services.*.name', 'numeric', 'min:0'],

                // Delivery Options
                'delivery_options' => ['nullable', 'array'],
                'delivery_options.*.type' => ['required', 'string', Rule::in(DeliveryOptionTypeEnum::values())],
                'delivery_options.*.price' => ['required', 'numeric', 'min:0'],
                'delivery_options.*.is_active' => ['required', 'boolean'],
                'delivery_options.*.is_default' => ['required', 'boolean'],

            ];
        }

        public function attributes()
        {
            return [
                'city_id' => __('validation.attributes.city'),
                'model_id' => __('validation.attributes.model'),
                'year_of_manufacture' => __('validation.attributes.year_of_manufacture'),
                'fuel_id' => __('validation.attributes.fuel'),
                'category_id' => __('validation.attributes.category'),
                'transmission_id' => __('validation.attributes.transmission'),
                'num_of_seat' => __('validation.attributes.num_of_seat'),
                'kilometers' => __('validation.attributes.kilometers'),
                'rental_shop_rule' => __('validation.attributes.rental_shop_rule'),

                // Images
                'images' => __('validation.attributes.images'),
                'images.*.image' => __('validation.attributes.image'),
                'images.*.image_name' => __('validation.attributes.image_name'),

                // Prices
                'prices' => __('validation.attributes.prices'),
                'prices.*.price' => __('validation.attributes.price'),
                'prices.*.duration_type' => __('validation.attributes.duration_type'),
                'prices.*.is_active' => __('validation.attributes.is_active'),

                // Mileage
                'mileages' => __('validation.attributes.mileages'),
                'mileages.limit_km_per_day' => __('validation.attributes.limit_km_per_day'),
                'mileages.limit_km_per_hour' => __('validation.attributes.limit_km_per_hour'),
                'mileages.extra_fee' => __('validation.attributes.extra_fee'),

                // Availabilities
                'availabilities' => __('validation.attributes.availabilities'),
                'availabilities.*.is_available' => __('validation.attributes.is_available'),
                'availabilities.*.unavailable_from' => __('validation.attributes.unavailable_from'),
                'availabilities.*.unavailable_to' => __('validation.attributes.unavailable_to'),
                'availabilities.*.reason' => __('validation.attributes.reason'),

                // Insurance
                'insurances' => __('validation.attributes.insurances'),
                'insurances.*.insurance_id' => __('validation.attributes.insurances'),

                // Extra Service
                'extra_services' => __('validation.attributes.extra_services'),
                'extra_services.*.extra_service_id' => __('validation.attributes.extra_services'),
                'extra_services.*.price' => __('validation.attributes.price'),

                // Custom Extra Service
                'custom_extra_services' => __('validation.attributes.custom_extra_services'),
                'custom_extra_services.*.name' => __('validation.attributes.custom_extra_services_name'),
                'custom_extra_services.*.description' => __('validation.attributes.custom_extra_services_description'),

                // Delivery Options
                'delivery_options' => __('validation.attributes.delivery_options'),
                'delivery_options.*.type' => __('validation.attributes.type'),
                'delivery_options.*.price' => __('validation.attributes.price'),
                'delivery_options.*.is_active' => __('validation.attributes.is_active'),
                'delivery_options.*.is_default' => __('validation.attributes.is_default'),

            ];
        }
    }
