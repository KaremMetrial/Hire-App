<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Booking;
use Illuminate\Support\Facades\Validator;

class MileageValidationService
{
    /**
     * Validate pickup mileage
     */
    public function validatePickupMileage(int $carId, int $pickupMileage): array
    {
        $car = Car::findOrFail($carId);
        $lastBooking = $this->getLastCompletedBooking($carId);

        $validator = Validator::make([
            'pickup_mileage' => $pickupMileage
        ], [
            'pickup_mileage' => [
                'required',
                'integer',
                'min:0',
                'max:1000000', // Reasonable maximum
                function ($attribute, $value, $fail) use ($lastBooking) {
                    if ($lastBooking && $value < $lastBooking->return_mileage) {
                        $fail("Pickup mileage ({$value}) cannot be less than the last recorded return mileage ({$lastBooking->return_mileage}).");
                    }
                },
                function ($attribute, $value, $fail) use ($car) {
                    // Check if mileage is reasonable for car's age
                    $carAge = $car->year ? now()->year - $car->year : 0;
                    $expectedMaxMileage = ($carAge * 15000) + 50000; // 15k per year + 50k base

                    if ($value > $expectedMaxMileage) {
                        $fail("Mileage ({$value}) seems unusually high for a {$carAge}-year-old car. Please verify the reading.");
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
                'warnings' => $this->getMileageWarnings($carId, $pickupMileage)
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'warnings' => $this->getMileageWarnings($carId, $pickupMileage)
        ];
    }

    /**
     * Validate return mileage
     */
    public function validateReturnMileage(int $bookingId, int $returnMileage): array
    {
        $booking = Booking::with('car')->findOrFail($bookingId);
        $pickupMileage = $booking->pickup_mileage;

        if (!$pickupMileage) {
            return [
                'valid' => false,
                'errors' => ['Pickup mileage must be recorded before validating return mileage'],
                'warnings' => []
            ];
        }

        $validator = Validator::make([
            'return_mileage' => $returnMileage,
            'pickup_mileage' => $pickupMileage
        ], [
            'return_mileage' => [
                'required',
                'integer',
                'min:' . $pickupMileage, // Must be >= pickup mileage
                function ($attribute, $value, $fail) use ($pickupMileage, $booking) {
                    $actualMileage = $value - $pickupMileage;
                    $rentalDays = $booking->getDurationInDays();

                    if ($rentalDays > 0) {
                        $dailyAverage = $actualMileage / $rentalDays;

                        // Check for unrealistic daily mileage
                        if ($dailyAverage > 1000) {
                            $fail("Daily mileage average ({$dailyAverage}) seems unrealistic. Please verify the reading.");
                        }

                        // Check for very low mileage (possible fraud)
                        if ($actualMileage < 10 && $rentalDays >= 1) {
                            $fail("Total mileage ({$actualMileage}) seems too low for {$rentalDays} day(s) of rental.");
                        }
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
                'warnings' => $this->getReturnMileageWarnings($booking, $returnMileage)
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'warnings' => $this->getReturnMileageWarnings($booking, $returnMileage)
        ];
    }

    /**
     * Calculate mileage fee with validation
     */
    public function calculateMileageFeeWithValidation(int $bookingId, int $returnMileage): array
    {
        $validation = $this->validateReturnMileage($bookingId, $returnMileage);

        if (!$validation['valid']) {
            return [
                'valid' => false,
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings'],
                'fee' => 0
            ];
        }

        $booking = Booking::with('car')->findOrFail($bookingId);
        $actualMileage = $returnMileage - $booking->pickup_mileage;

        // Calculate fee using existing logic
        $mileageConfig = config('booking.mileage');
        $car = $booking->car;
        $includedMileage = $car->mileages->daily_mileage_limit ?? $mileageConfig['daily_limit'];
        $extraMileage = max(0, $actualMileage - ($includedMileage * $booking->getDurationInDays()));
        $mileageRate = $car->mileages->extra_mileage_rate ?? $mileageConfig['extra_rate'];
        $fee = $extraMileage * $mileageRate;

        return [
            'valid' => true,
            'errors' => [],
            'warnings' => $validation['warnings'],
            'fee' => $fee,
            'actual_mileage' => $actualMileage,
            'included_mileage' => $includedMileage * $booking->getDurationInDays(),
            'extra_mileage' => $extraMileage,
            'rate_per_mile' => $mileageRate
        ];
    }

    /**
     * Get mileage warnings
     */
    private function getMileageWarnings(int $carId, int $mileage): array
    {
        $warnings = [];
        $car = Car::findOrFail($carId);

        // Warning for unusually high mileage
        if ($mileage > 200000) {
            $warnings[] = "High mileage detected ({$mileage}). Please ensure this is correct.";
        }

        // Warning for round numbers (possible estimation)
        if ($mileage % 1000 === 0 && $mileage > 10000) {
            $warnings[] = "Mileage is a round number. Please verify the exact reading.";
        }

        return $warnings;
    }

    /**
     * Get return mileage warnings
     */
    private function getReturnMileageWarnings(Booking $booking, int $returnMileage): array
    {
        $warnings = [];
        $actualMileage = $returnMileage - $booking->pickup_mileage;
        $rentalDays = $booking->getDurationInDays();

        if ($rentalDays > 0) {
            $dailyAverage = $actualMileage / $rentalDays;

            // Warning for high daily usage
            if ($dailyAverage > 300) {
                $warnings[] = "High daily mileage usage ({$dailyAverage} km/day). This may incur additional charges.";
            }

            // Warning for very low usage
            if ($dailyAverage < 10 && $rentalDays > 1) {
                $warnings[] = "Very low mileage usage. Please confirm the mileage readings are correct.";
            }
        }

        return $warnings;
    }

    /**
     * Get last completed booking for a car
     */
    private function getLastCompletedBooking(int $carId): ?Booking
    {
        return Booking::where('car_id', $carId)
            ->where('status', 'completed')
            ->whereNotNull('return_mileage')
            ->orderBy('return_date', 'desc')
            ->first();
    }

    /**
     * Get mileage statistics for a car
     */
    public function getMileageStatistics(int $carId): array
    {
        $bookings = Booking::where('car_id', $carId)
            ->where('status', 'completed')
            ->whereNotNull('pickup_mileage')
            ->whereNotNull('return_mileage')
            ->get();

        if ($bookings->isEmpty()) {
            return [
                'total_bookings' => 0,
                'average_daily_mileage' => 0,
                'total_mileage' => 0,
                'last_mileage' => null
            ];
        }

        $totalMileage = $bookings->sum('actual_mileage_used');
        $totalDays = $bookings->sum(function ($booking) {
            return $booking->getDurationInDays();
        });

        return [
            'total_bookings' => $bookings->count(),
            'average_daily_mileage' => $totalDays > 0 ? round($totalMileage / $totalDays, 2) : 0,
            'total_mileage' => $totalMileage,
            'last_mileage' => $bookings->last()->return_mileage,
            'average_trip_mileage' => round($totalMileage / $bookings->count(), 2)
        ];
    }
}
