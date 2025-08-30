<?php

    namespace App\Http\Requests\Vendor;

    use App\Models\Vendor;
    use App\Rules\ValidOtpRule;
    use Illuminate\Foundation\Http\FormRequest;
    use App\Services\OtpService;

    class RegisterRequest extends FormRequest
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
            $otpService = app(\App\Services\OtpService::class);
            return [
                // OTP
                'otp_data' => ['required', 'array'],
                'otp_data.identifier' => ['required', 'string'],
                'otp_data.otp' => ['required', 'string'],
                'otp_data' => [new ValidOtpRule($otpService, 'vendor')],

                // Vendor Data
                'vendor' => ['required', 'array'],
                'vendor.name' => ['required', 'string', 'max:255'],
                'vendor.email' => ['required', 'string', 'email', 'max:255', 'unique:vendors,email'],
                'vendor.phone' => ['required', 'string', 'max:255', 'unique:vendors,phone'],
                'vendor.password' => ['required', 'string', 'confirmed', 'min:8', 'max:255'],
                'vendor.national_id_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],

                // Rental Data
                'rental_shop' => ['required', 'array'],
                'rental_shop.name' => ['required', 'string', 'max:255'],
                'rental_shop.phone' => ['required', 'string', 'max:255'],
                'rental_shop.image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'rental_shop.transport_license_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'rental_shop.commerical_registration_photo' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,svg',
                    'max:2048'
                ],

                // Rental Shop Address
                'rental_shop.address' => ['nullable', 'array'],
                'rental_shop.address.latitude' => ['nullable', 'string'],
                'rental_shop.address.longitude' => ['nullable', 'string'],
                'rental_shop.address.country_id' => ['required', 'integer', 'exists:countries,id']
            ];
        }
        /*
         * Custom Attributes Name for Validation messages
         */
        public function attributes(): array
        {
            return [
                'otp_data' => __('validation.attributes.otp_code'),

                'vendor.name' => __('validation.attributes.vendor.name'),
                'vendor.email' => __('validation.attributes.vendor.email'),
                'vendor.phone' => __('validation.attributes.vendor.phone'),
                'vendor.password' => __('validation.attributes.vendor.password'),
                'vendor.national_id_photo' => __('validation.attributes.vendor.national_id_photo'),

                'rental_shop.name' => __('validation.attributes.rentalShop.name'),
                'rental_shop.phone' => __('validation.attributes.rentalShop.phone'),
                'rental_shop.image' => __('validation.attributes.rentalShop.image'),
                'rental_shop.transport_license_photo' => __('validation.attributes.rentalShop.transport_license_photo'),
                'rental_shop.commerical_registration_photo' => __('validation.attributes.rentalShop.commerical_registration_photo'),

                'rental_shop.address.latitude' => __('validation.attributes.rentalShop.address.latitude'),
                'rental_shop.address.longitude' => __('validation.attributes.rentalShop.address.longitude'),
                'rental_shop.address.country_id' => __('validation.attributes.rentalShop.address.country_id'),
            ];
        }
    }
