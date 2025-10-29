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
            'pickup_latitude' => ['required_if:pickup_location_type,custom', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required_if:pickup_location_type,custom', 'numeric', 'between:-180,180'],
            'return_address' => ['required_if:return_location_type,custom', 'string', 'max:255'],
            'return_latitude' => ['required_if:return_location_type,custom', 'numeric', 'between:-90,90'],
            'return_longitude' => ['required_if:return_location_type,custom', 'numeric', 'between:-180,180'],
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

            // Check if user already has an active booking for this car
            if ($this->car_id) {
                $existingBooking = \App\Models\Booking::where('user_id', auth()->id())
                    ->where('car_id', $this->car_id)
                    ->whereIn('status', [
                        \App\Enums\BookingStatusEnum::Pending->value,
                        \App\Enums\BookingStatusEnum::Confirmed->value,
                        \App\Enums\BookingStatusEnum::Active->value
                    ])
                    ->exists();

                if ($existingBooking) {
                    $validator->errors()->add('car_id', 'You already have an active booking for this car. You cannot make multiple bookings for the same car.');
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

            // Create reservation to check availability and lock the car
            if ($this->car_id && $this->pickup_date && $this->return_date) {
                try {
                    $reservationService = app(\App\Services\BookingReservationService::class);

                    // Debug log
                    \Log::info('Attempting to create reservation', [
                        'car_id' => $this->car_id,
                        'pickup_date' => $this->pickup_date,
                        'return_date' => $this->return_date,
                        'user_id' => auth()->id()
                    ]);

                    $reservationToken = $reservationService->createReservation(
                        $this->car_id,
                        $this->pickup_date,
                        $this->return_date,
                        auth()->id()
                    );

                    // Debug log
                    \Log::info('Reservation created successfully', [
                        'token' => $reservationToken
                    ]);

                    // Store token in request for later use
                    $this->merge(['reservation_token' => $reservationToken]);
                } catch (\Exception $e) {
                    // Log the actual error
                    \Log::error('Reservation service error: ' . $e->getMessage(), [
                        'exception' => $e,
                        'car_id' => $this->car_id,
                        'pickup_date' => $this->pickup_date,
                        'return_date' => $this->return_date,
                        'user_id' => auth()->id()
                    ]);

                    // Fallback to original availability check if reservation service fails
                    if (!$this->isCarAvailable($this->car_id, $this->pickup_date, $this->return_date)) {
                        $validator->errors()->add('car_id', 'Car is not available for the selected dates');
                    } else {
                        // If car is available but reservation service failed, log a warning
                        \Log::warning('Reservation service failed but car is available', [
                            'car_id' => $this->car_id,
                            'pickup_date' => $this->pickup_date,
                            'return_date' => $this->return_date
                        ]);
                    }
                }
            }

            // Validate extra services availability and pricing
            if ($this->car_id && !empty($this->extra_services)) {
                $this->validateExtraServices($validator);
            }

            // Validate insurance availability
            if ($this->car_id && $this->insurance_id) {
                $this->validateInsurance($validator);
            }
        });
    }

    /**
     * Check if car is available for given dates
     */
    private function isCarAvailable(int $carId, string $pickupDate, string $returnDate): bool
    {
        $conflictingBookings = \App\Models\Booking::where('car_id', $carId)
            ->whereIn('status', [\App\Enums\BookingStatusEnum::Confirmed->value, \App\Enums\BookingStatusEnum::Active->value])
            ->where(function ($query) use ($pickupDate, $returnDate) {
                $query->whereBetween('pickup_date', [$pickupDate, $returnDate])
                    ->orWhereBetween('return_date', [$pickupDate, $returnDate])
                    ->orWhere(function ($q) use ($pickupDate, $returnDate) {
                        $q->where('pickup_date', '<=', $pickupDate)
                            ->where('return_date', '>=', $returnDate);
                    });
            })
            ->exists();

        return !$conflictingBookings;
    }

    /**
     * Validate extra services availability and pricing
     */
    private function validateExtraServices($validator): void
    {
        $car = \App\Models\Car::with(['services'])->findOrFail($this->car_id);

        foreach ($this->extra_services as $index => $service) {
            $carExtraService = $car->services()
                ->where('extra_service_id', $service['id'])
                ->first();

            if (!$carExtraService) {
                $validator->errors()->add("extra_services.{$index}.id", "Extra service with ID {$service['id']} is not available for this car");
                continue;
            }

            if (is_null($carExtraService->pivot->price)) {
                $validator->errors()->add("extra_services.{$index}.id", "Extra service with ID {$service['id']} has no price configured");
            }
        }
    }

    /**
     * Validate insurance availability
     */
    private function validateInsurance($validator): void
    {
        $car = \App\Models\Car::with(['insurances'])->findOrFail($this->car_id);
        $carInsurance = $car->insurances()
            ->where('insurances.id', $this->insurance_id)
            ->first();

        if (!$carInsurance) {
            $validator->errors()->add('insurance_id', "Insurance with ID {$this->insurance_id} is not available for this car");
        }
    }
}
