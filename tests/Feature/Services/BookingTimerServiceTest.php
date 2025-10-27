<?php

namespace Tests\Feature\Services;

use App\Models\Booking;
use App\Models\RentalShop;
use App\Models\WorkingDay;
use App\Services\BookingTimerService;
use Carbon\Carbon;
use Tests\TestCase;
use App\Enums\DayOfWeekEnum;
use App\Enums\BookingStatusEnum;

class BookingTimerServiceTest extends TestCase
{
    private BookingTimerService $timerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timerService = app(BookingTimerService::class);
    }

    /** @test */
    public function it_calculates_timer_start_time_during_working_hours()
    {
        // Create a rental shop with working hours (9 AM - 6 PM)
        $rentalShop = RentalShop::factory()->create();
        WorkingDay::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'day_of_week' => DayOfWeekEnum::MONDAY,
            'open_time' => '09:00',
            'close_time' => '18:00',
        ]);

        // Create a booking during working hours (10 AM)
        $bookingTime = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(10, 0);
        $booking = Booking::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'status' => BookingStatusEnum::Pending->value,
            'created_at' => $bookingTime,
        ]);

        $timerStartTime = $this->timerService->calculateActualTimerStartTime($booking);

        // Timer should start immediately since it's during working hours
        $this->assertEquals($bookingTime->format('Y-m-d H:i'), $timerStartTime->format('Y-m-d H:i'));
    }

    /** @test */
    public function it_calculates_timer_start_time_before_working_hours()
    {
        // Create a rental shop with working hours (9 AM - 6 PM)
        $rentalShop = RentalShop::factory()->create();
        WorkingDay::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'day_of_week' => DayOfWeekEnum::MONDAY,
            'open_time' => '09:00',
            'close_time' => '18:00',
        ]);

        // Create a booking before working hours (4 AM)
        $bookingTime = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(4, 0);
        $booking = Booking::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'status' => BookingStatusEnum::Pending->value,
            'created_at' => $bookingTime,
        ]);

        $timerStartTime = $this->timerService->calculateActualTimerStartTime($booking);

        // Timer should start at 9 AM (opening time)
        $this->assertEquals('09:00', $timerStartTime->format('H:i'));
        $this->assertEquals($bookingTime->format('Y-m-d'), $timerStartTime->format('Y-m-d'));
    }

    /** @test */
    public function it_checks_if_current_time_is_within_working_hours()
    {
        // Create a rental shop with working hours (9 AM - 6 PM)
        $rentalShop = RentalShop::factory()->create();
        WorkingDay::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'day_of_week' => DayOfWeekEnum::MONDAY,
            'open_time' => '09:00',
            'close_time' => '18:00',
        ]);

        // Test during working hours
        $workingHoursTime = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(14, 0);
        $this->assertTrue($this->timerService->isWithinWorkingHours($rentalShop, $workingHoursTime));

        // Test before working hours
        $beforeWorkingHours = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(7, 0);
        $this->assertFalse($this->timerService->isWithinWorkingHours($rentalShop, $beforeWorkingHours));

        // Test after working hours
        $afterWorkingHours = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(20, 0);
        $this->assertFalse($this->timerService->isWithinWorkingHours($rentalShop, $afterWorkingHours));
    }

    /** @test */
    public function it_calculates_remaining_acceptance_time_considering_working_hours()
    {
        // Create a rental shop with working hours (9 AM - 6 PM)
        $rentalShop = RentalShop::factory()->create();
        WorkingDay::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'day_of_week' => DayOfWeekEnum::MONDAY,
            'open_time' => '09:00',
            'close_time' => '18:00',
        ]);

        // Create a booking at 4 AM (before working hours)
        $bookingTime = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(4, 0);
        $booking = Booking::factory()->create([
            'rental_shop_id' => $rentalShop->id,
            'status' => BookingStatusEnum::Pending->value,
            'created_at' => $bookingTime,
        ]);

        // Check remaining time at 10 AM (1 hour into working hours)
        $currentTime = Carbon::now()->next(DayOfWeekEnum::MONDAY->name)->setTime(10, 0);
        $remainingTime = $this->timerService->calculateRemainingAcceptanceTime($booking, 1440); // 24 hours

        // Should have 23 hours remaining (24 - 1 hour elapsed)
        $this->assertEquals(1380, $remainingTime['remaining_minutes']); // 23 hours in minutes
        $this->assertFalse($remainingTime['is_expired']);
        $this->assertTrue($remainingTime['is_within_working_hours']);
    }
}
