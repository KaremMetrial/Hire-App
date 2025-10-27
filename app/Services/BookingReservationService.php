<?php

namespace App\Services;

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookingReservationService
{
    private const RESERVATION_TTL = 900; // 15 minutes
    private const LOCK_PREFIX = 'car_booking_lock_';

    /**
     * Create a temporary reservation for a car
     */
    public function createReservation(int $carId, string $pickupDate, string $returnDate, int $userId): string
    {
        $lockKey = self::LOCK_PREFIX . $carId;

        return DB::transaction(function () use ($carId, $pickupDate, $returnDate, $userId, $lockKey) {
            // Acquire database lock
            $lock = DB::raw("GET_LOCK('{$lockKey}', 10)");

            if (!$lock) {
                throw new \Exception('Unable to acquire lock for car reservation');
            }

            try {
                // Check if car is actually available
                if (!$this->isCarAvailable($carId, $pickupDate, $returnDate)) {
                    throw new \Exception('Car is not available for the selected dates');
                }

                // Generate unique reservation token
                $reservationToken = uniqid('res_', true);

                // Store reservation in cache with TTL
                $reservationData = [
                    'car_id' => $carId,
                    'pickup_date' => $pickupDate,
                    'return_date' => $returnDate,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'expires_at' => now()->addSeconds(self::RESERVATION_TTL)
                ];

                Cache::put(
                    "car_reservation_{$reservationToken}",
                    $reservationData,
                    self::RESERVATION_TTL
                );

                return $reservationToken;

            } finally {
                // Release the lock
                DB::raw("RELEASE_LOCK('{$lockKey}')");
            }
        });
    }

    /**
     * Confirm a reservation by creating the actual booking
     */
    public function confirmReservation(string $reservationToken, array $bookingData): Booking
    {
        $reservation = Cache::get("car_reservation_{$reservationToken}");

        if (!$reservation) {
            throw new \Exception('Reservation expired or not found');
        }

        if ($reservation['user_id'] !== auth()->id()) {
            throw new \Exception('Invalid reservation for this user');
        }

        return DB::transaction(function () use ($reservation, $bookingData, $reservationToken) {
            // Double-check availability before creating booking
            if (!$this->isCarAvailable($reservation['car_id'], $reservation['pickup_date'], $reservation['return_date'])) {
                throw new \Exception('Car became unavailable. Please try again.');
            }

            // Create the actual booking
            $booking = Booking::create([
                'user_id' => $reservation['user_id'],
                'car_id' => $reservation['car_id'],
                'pickup_date' => $reservation['pickup_date'],
                'return_date' => $reservation['return_date'],
                'rental_shop_id' => Car::findOrFail($reservation['car_id'])->rental_shop_id,
                'status' => BookingStatusEnum::Pending->value,
                'payment_status' => 'unpaid',
                ...$bookingData
            ]);

            // Remove the reservation
            Cache::forget("car_reservation_{$reservationToken}");

            return $booking;
        });
    }

    /**
     * Check if car is available for given dates
     */
    private function isCarAvailable(int $carId, string $pickupDate, string $returnDate): bool
    {
        // Check existing bookings
        $existingBookings = Booking::where('car_id', $carId)
            ->whereIn('status', [BookingStatusEnum::Confirmed->value, BookingStatusEnum::Active->value])
            ->where(function ($query) use ($pickupDate, $returnDate) {
                $query->whereBetween('pickup_date', [$pickupDate, $returnDate])
                    ->orWhereBetween('return_date', [$pickupDate, $returnDate])
                    ->orWhere(function ($q) use ($pickupDate, $returnDate) {
                        $q->where('pickup_date', '<=', $pickupDate)
                            ->where('return_date', '>=', $returnDate);
                    });
            })
            ->exists();

        if ($existingBookings) {
            return false;
        }

        // Check active reservations
        $activeReservations = Cache::getMatchingKeys("car_reservation_*");

        foreach ($activeReservations as $key) {
            $reservation = Cache::get($key);
            if ($reservation &&
                $reservation['car_id'] == $carId &&
                $this->datesOverlap($reservation['pickup_date'], $reservation['return_date'], $pickupDate, $returnDate)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if two date ranges overlap
     */
    private function datesOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $s1 = Carbon::parse($start1);
        $e1 = Carbon::parse($end1);
        $s2 = Carbon::parse($start2);
        $e2 = Carbon::parse($end2);

        return !($e1->lt($s2) || $s1->gt($e2));
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation(string $reservationToken): bool
    {
        return Cache::forget("car_reservation_{$reservationToken}");
    }

    /**
     * Clean up expired reservations
     */
    public function cleanupExpiredReservations(): int
    {
        $keys = Cache::getMatchingKeys("car_reservation_*");
        $cleaned = 0;

        foreach ($keys as $key) {
            $reservation = Cache::get($key);
            if ($reservation && Carbon::parse($reservation['expires_at'])->isPast()) {
                Cache::forget($key);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
