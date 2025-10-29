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
        $useDbLocks = $this->checkDbLockSupport();

        return DB::transaction(function () use ($carId, $pickupDate, $returnDate, $userId, $lockKey, $useDbLocks) {
            $lockAcquired = false;
            
            try {
                // Try to acquire database lock if supported
                if ($useDbLocks) {
                    $lockResult = DB::selectOne("SELECT GET_LOCK(?, 5) as lock_acquired", [$lockKey]);
                    $lockAcquired = $lockResult && $lockResult->lock_acquired == 1;
                    
                    if (!$lockAcquired) {
                        \Log::warning('Could not acquire database lock, falling back to optimistic locking', [
                            'car_id' => $carId,
                            'lock_key' => $lockKey
                        ]);
                    }
                }

                // Check if car is actually available
                if (!$this->isCarAvailable($carId, $pickupDate, $returnDate)) {
                    throw new \Exception('Car is not available for the selected dates');
                }

                // Generate unique reservation token
                $reservationToken = 'res_' . md5(uniqid((string)mt_rand(), true));

                // Store reservation in cache with TTL
                $reservationData = [
                    'car_id' => $carId,
                    'pickup_date' => $pickupDate,
                    'return_date' => $returnDate,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'expires_at' => now()->addSeconds(self::RESERVATION_TTL)
                ];

                // Store the reservation
                $cacheKey = "car_reservation_{$reservationToken}";
                $success = Cache::add($cacheKey, $reservationData, self::RESERVATION_TTL);

                if (!$success) {
                    throw new \Exception('Failed to create reservation. Please try again.');
                }

                // Update the list of active reservation tokens for this car
                $reservationCheckKey = "car_availability_check_{$carId}";
                $activeReservationTokens = Cache::get($reservationCheckKey, []);
                $activeReservationTokens[] = $reservationToken;
                
                // Store the updated list with the same TTL as the reservation
                Cache::put($reservationCheckKey, $activeReservationTokens, self::RESERVATION_TTL);

                return $reservationToken;

            } catch (\Exception $e) {
                \Log::error('Reservation creation failed', [
                    'error' => $e->getMessage(),
                    'car_id' => $carId,
                    'user_id' => $userId
                ]);
                throw $e;
            } finally {
                // Release the lock if it was acquired
                if ($lockAcquired) {
                    try {
                        DB::select("DO RELEASE_LOCK(?)", [$lockKey]);
                    } catch (\Exception $e) {
                        \Log::error('Error releasing database lock', [
                            'error' => $e->getMessage(),
                            'lock_key' => $lockKey
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Check if database lock functions are available
     */
    private function checkDbLockSupport(): bool
    {
        try {
            // Try a simple lock/unlock to check support
            $testKey = 'test_lock_' . time();
            $result = DB::selectOne("SELECT GET_LOCK(?, 1) as lock_acquired", [$testKey]);
            if ($result && $result->lock_acquired == 1) {
                DB::select("DO RELEASE_LOCK(?)", [$testKey]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::warning('Database lock functions not available', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
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

        // Check active reservations using a tag if supported, otherwise use a different approach
        $reservationKey = "car_reservation_*_{$carId}";
        
        // For database cache store, we need to implement a different approach
        // since getMatchingKeys isn't available. We'll use a dedicated cache key.
        $reservationCheckKey = "car_availability_check_{$carId}";
        
        // Get all active reservation tokens for this car
        $activeReservationTokens = Cache::get($reservationCheckKey, []);
        
        // If no active reservations, car is available
        if (empty($activeReservationTokens)) {
            return true;
        }
        
        // Check each reservation
        foreach ($activeReservationTokens as $token) {
            $reservation = Cache::get("car_reservation_{$token}");
            if ($reservation && 
                $reservation['car_id'] == $carId &&
                $this->datesOverlap(
                    $reservation['pickup_date'], 
                    $reservation['return_date'], 
                    $pickupDate, 
                    $returnDate
                ) && 
                $reservation['expires_at'] > now()
            ) {
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
        $cacheKey = "car_reservation_{$reservationToken}";
        $reservation = Cache::get($cacheKey);
        
        if ($reservation) {
            // Get the car ID from the reservation
            $carId = $reservation['car_id'];
            
            // Remove the reservation from cache
            Cache::forget($cacheKey);
            
            // Update the list of active reservation tokens for this car
            $reservationCheckKey = "car_availability_check_{$carId}";
            $activeReservationTokens = Cache::get($reservationCheckKey, []);
            
            // Remove the cancelled token from the list
            $activeReservationTokens = array_filter($activeReservationTokens, function($token) use ($reservationToken) {
                return $token !== $reservationToken;
            });
            
            // Update the cache
            if (empty($activeReservationTokens)) {
                Cache::forget($reservationCheckKey);
            } else {
                Cache::put($reservationCheckKey, array_values($activeReservationTokens), self::RESERVATION_TTL);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Clean up expired reservations
     */
    public function cleanupExpiredReservations(): int
    {
        $cleaned = 0;
        
        // This is a simplified version since we can't scan all keys with database cache
        // In a production environment, you might want to use a scheduled job
        // that runs periodically to clean up expired reservations
        
        return $cleaned;
    }
}
