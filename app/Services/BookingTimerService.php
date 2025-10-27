<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RentalShop;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Exception;

class BookingTimerService
{
    /**
     * Default acceptance time in minutes (24 hours)
     */
    private const DEFAULT_ACCEPTANCE_TIME_MINUTES = 1440;

    /**
     * Check if the current time is within working hours for a rental shop
     *
     * @param RentalShop $rentalShop
     * @param Carbon|null $currentTime
     * @return bool
     */
    public function isWithinWorkingHours(RentalShop $rentalShop, ?Carbon $currentTime = null): bool
    {
        $currentTime = $currentTime ?? now();
        $currentDayOfWeek = $this->getCurrentDayOfWeekValue($currentTime);

        // Get working hours for the current day
        $workingDay = $rentalShop->workingDays()
            ->where('day_of_week', $currentDayOfWeek)
            ->first();

        // If no working day is configured, assume 24/7
        if (!$workingDay) {
            return true;
        }

        // Check if current time is between open_time and close_time
        $currentTimeOnly = $currentTime->format('H:i');
        $openTime = $workingDay->open_time->format('H:i');
        $closeTime = $workingDay->close_time->format('H:i');

        return $currentTimeOnly >= $openTime && $currentTimeOnly <= $closeTime;
    }

    /**
     * Get the next opening time for a rental shop
     *
     * @param RentalShop $rentalShop
     * @param Carbon|null $fromTime
     * @return Carbon|null
     */
    public function getNextOpeningTime(RentalShop $rentalShop, ?Carbon $fromTime = null): ?Carbon
    {
        $fromTime = $fromTime ?? now();

        // If currently within working hours, return the current time
        if ($this->isWithinWorkingHours($rentalShop, $fromTime)) {
            return $fromTime;
        }

        // Search for the next opening time in the next 7 days
        $searchTime = $fromTime->copy();

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $checkTime = $searchTime->copy()->addDays($dayOffset);
            $dayOfWeek = $this->getCurrentDayOfWeekValue($checkTime);

            $workingDay = $rentalShop->workingDays()
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($workingDay) {
                if ($dayOffset === 0) {
                    // Check today - if current time is before opening time, return today's opening time
                    $currentTimeOnly = $fromTime->format('H:i');
                    $openTime = $workingDay->open_time->format('H:i');

                    if ($currentTimeOnly < $openTime) {
                        return $fromTime->copy()->setTimeFrom($workingDay->open_time);
                    }
                } else {
                    // For future days, return the opening time
                    return $checkTime->copy()->setTimeFrom($workingDay->open_time);
                }
            }
        }

        // If no working hours found in the next 7 days, return null
        return null;
    }

    /**
     * Calculate the actual timer start time based on working hours
     * This is when the acceptance timer should actually start counting
     *
     * @param Booking $booking
     * @return Carbon
     */
    public function calculateActualTimerStartTime(Booking $booking): Carbon
    {
        $bookingCreatedAt = $booking->created_at;
        $rentalShop = $booking->rentalShop;

        // If the booking was created during working hours, timer starts immediately
        if ($this->isWithinWorkingHours($rentalShop, $bookingCreatedAt)) {
            return $bookingCreatedAt;
        }

        // Otherwise, timer starts at the next opening time
        $nextOpeningTime = $this->getNextOpeningTime($rentalShop, $bookingCreatedAt);

        if ($nextOpeningTime) {
            return $nextOpeningTime;
        }

        // Fallback: if no working hours configured, start immediately
        return $bookingCreatedAt;
    }

    /**
     * Calculate the remaining acceptance time considering working hours
     *
     * @param Booking $booking
     * @param int $acceptanceTimeMinutes
     * @return array
     */
    public function calculateRemainingAcceptanceTime(Booking $booking, int $acceptanceTimeMinutes = self::DEFAULT_ACCEPTANCE_TIME_MINUTES): array
    {
        // Only calculate for pending bookings
        if ($booking->status !== 'pending') {
            return [
                'remaining_minutes' => 0,
                'remaining_seconds' => 0,
                'is_expired' => true,
                'timer_start_time' => null,
                'timer_end_time' => null,
                'is_within_working_hours' => false,
            ];
        }

        $actualTimerStartTime = $this->calculateActualTimerStartTime($booking);
        $currentTime = now();
        $rentalShop = $booking->rentalShop;

        // Calculate timer end time (acceptance deadline)
        $timerEndTime = $actualTimerStartTime->copy()->addMinutes($acceptanceTimeMinutes);

        // If current time is before timer start time, no time has elapsed yet
        if ($currentTime->lt($actualTimerStartTime)) {
            return [
                'remaining_minutes' => $acceptanceTimeMinutes,
                'remaining_seconds' => $acceptanceTimeMinutes * 60,
                'is_expired' => false,
                'timer_start_time' => $actualTimerStartTime,
                'timer_end_time' => $timerEndTime,
                'is_within_working_hours' => $this->isWithinWorkingHours($rentalShop, $currentTime),
            ];
        }

        // Calculate elapsed working hours only
        $elapsedWorkingMinutes = $this->calculateElapsedWorkingMinutes(
            $actualTimerStartTime,
            $currentTime,
            $rentalShop
        );

        // Calculate remaining time
        $remainingMinutes = max(0, $acceptanceTimeMinutes - $elapsedWorkingMinutes);
        $isExpired = $remainingMinutes <= 0;

        return [
            'remaining_minutes' => $remainingMinutes,
            'remaining_seconds' => $remainingMinutes * 60,
            'is_expired' => $isExpired,
            'timer_start_time' => $actualTimerStartTime,
            'timer_end_time' => $timerEndTime,
            'is_within_working_hours' => $this->isWithinWorkingHours($rentalShop, $currentTime),
        ];
    }

    /**
     * Calculate elapsed working minutes between two timestamps
     *
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param RentalShop $rentalShop
     * @return int
     */
    private function calculateElapsedWorkingMinutes(Carbon $startTime, Carbon $endTime, RentalShop $rentalShop): int
    {
        $totalWorkingMinutes = 0;
        $currentTime = $startTime->copy();

        while ($currentTime->lt($endTime)) {
            $dayOfWeek = $this->getCurrentDayOfWeekValue($currentTime);
            $workingDay = $rentalShop->workingDays()
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($workingDay) {
                // Get working hours for this day
                $dayStart = $currentTime->copy()->setTimeFrom($workingDay->open_time);
                $dayEnd = $currentTime->copy()->setTimeFrom($workingDay->close_time);

                // Calculate overlap between current time range and working hours
                $periodStart = max($currentTime, $dayStart);
                $periodEnd = min($endTime, $dayEnd);

                if ($periodEnd->gt($periodStart)) {
                    $totalWorkingMinutes += $periodEnd->diffInMinutes($periodStart);
                }
            } else {
                // If no working hours configured, count all time as working time
                $totalWorkingMinutes += min(1440, $endTime->diffInMinutes($currentTime));
                break;
            }

            // Move to next day
            $currentTime = $currentTime->copy()->startOfDay()->addDay();
        }

        return $totalWorkingMinutes;
    }

    /**
     * Get the current day of week value matching the enum
     *
     * @param Carbon $date
     * @return int
     */
    private function getCurrentDayOfWeekValue(Carbon $date): int
    {
        // Carbon: Sunday = 0, Saturday = 6
        // Our enum: Saturday = 1, Sunday = 2, Monday = 3, etc.
        $carbonDay = $date->dayOfWeek;

        return match ($carbonDay) {
            0 => 2, // Sunday
            1 => 3, // Monday
            2 => 4, // Tuesday
            3 => 5, // Wednesday
            4 => 6, // Thursday
            5 => 7, // Friday
            6 => 1, // Saturday
        };
    }

    /**
     * Get working hours for a rental shop for the week
     *
     * @param RentalShop $rentalShop
     * @return array
     */
    public function getWeeklyWorkingHours(RentalShop $rentalShop): array
    {
        $workingHours = [];

        foreach ($rentalShop->workingDays as $workingDay) {
            $workingHours[] = [
                'day_of_week' => $workingDay->day_of_week,
                'day_label' => $workingDay->day_of_week->label(),
                'open_time' => $workingDay->open_time->format('H:i'),
                'close_time' => $workingDay->close_time->format('H:i'),
            ];
        }

        return $workingHours;
    }
}
