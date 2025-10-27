<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use App\Services\MileageValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MileageValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private MileageValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MileageValidationService::class);
    }

    /** @test */
    public function it_validates_pickup_mileage_correctly()
    {
        $car = Car::factory()->create(['year_of_manufacture' => 2020]);
        $pickupMileage = 50000;

        $result = $this->service->validatePickupMileage($car->id, $pickupMileage);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_rejects_pickup_mileage_less_than_last_return()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();

        // Create a completed booking with return mileage
        Booking::factory()->create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'return_mileage' => 60000,
            'completed_at' => now()->subDays(10)
        ]);

        $result = $this->service->validatePickupMileage($car->id, 55000);

        $this->assertFalse($result['valid']);
        $this->assertContains('Pickup mileage (55000) cannot be less than last recorded return mileage (60000)', $result['errors']);
    }

    /** @test */
    public function it_warns_for_unusually_high_mileage()
    {
        $car = Car::factory()->create(['year_of_manufacture' => 2020]); // 4 years old
        $pickupMileage = 200000; // Very high for 4-year-old car

        $result = $this->service->validatePickupMileage($car->id, $pickupMileage);

        $this->assertTrue($result['valid']);
        $this->assertNotEmpty($result['warnings']);
        $this->assertContains('High mileage detected (200000). Please ensure this is correct.', $result['warnings']);
    }

    /** @test */
    public function it_validates_return_mileage_correctly()
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'car_id' => $car->id,
            'pickup_mileage' => 50000,
            'status' => 'active',
            'pickup_date' => now()->subDay(),
            'return_date' => now()->addDay()
        ]);

        $result = $this->service->validateReturnMileage($booking->id, 55000);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_rejects_return_mileage_less_than_pickup()
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'car_id' => $car->id,
            'pickup_mileage' => 50000,
            'status' => 'active'
        ]);

        $result = $this->service->validateReturnMileage($booking->id, 45000);

        $this->assertFalse($result['valid']);
        $this->assertContains('Return mileage must be greater than or equal to pickup mileage', $result['errors']);
    }

    /** @test */
    public function it_warns_for_unrealistic_daily_mileage()
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'car_id' => $car->id,
            'pickup_mileage' => 50000,
            'status' => 'active',
            'pickup_date' => now()->subDays(2),
            'return_date' => now() // 2 days rental
        ]);

        // 2000 miles in 2 days = 1000 miles/day (unrealistic)
        $result = $this->service->validateReturnMileage($booking->id, 52000);

        $this->assertFalse($result['valid']);
        $this->assertContains('Daily mileage average (1000) seems unrealistic. Please verify the reading.', $result['errors']);
    }

    /** @test */
    public function it_calculates_mileage_fee_correctly()
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'car_id' => $car->id,
            'pickup_mileage' => 50000,
            'status' => 'active',
            'pickup_date' => now()->subDays(3),
            'return_date' => now() // 3 days rental
        ]);

        // 600 miles total, 3 days = 200 miles/day (within limit of 200 miles/day)
        $result = $this->service->calculateMileageFeeWithValidation($booking->id, 50600);

        $this->assertTrue($result['valid']);
        $this->assertEquals(600, $result['actual_mileage']);
        $this->assertEquals(600, $result['included_mileage']); // 200 * 3 days
        $this->assertEquals(0, $result['extra_mileage']);
        $this->assertEquals(0, $result['fee']);
    }

    /** @test */
    public function it_calculates_extra_mileage_fee()
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'car_id' => $car->id,
            'pickup_mileage' => 50000,
            'status' => 'active',
            'pickup_date' => now()->subDays(3),
            'return_date' => now() // 3 days rental
        ]);

        // 1200 miles total, 3 days = 400 miles/day (200 over limit)
        $result = $this->service->calculateMileageFeeWithValidation($booking->id, 51200);

        $this->assertTrue($result['valid']);
        $this->assertEquals(1200, $result['actual_mileage']);
        $this->assertEquals(600, $result['included_mileage']); // 200 * 3 days
        $this->assertEquals(600, $result['extra_mileage']);
        $this->assertEquals(300, $result['fee']); // 600 * 0.50 per mile
    }

    /** @test */
    public function it_provides_mileage_statistics()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();

        // Create multiple completed bookings
        Booking::factory()->count(3)->create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'actual_mileage_used' => 300,
            'pickup_date' => now()->subDays(10),
            'return_date' => now()->subDays(5)
        ]);

        $stats = $this->service->getMileageStatistics($car->id);

        $this->assertEquals(3, $stats['total_bookings']);
        $this->assertEquals(300, $stats['average_trip_mileage']);
        $this->assertEquals(900, $stats['total_mileage']);
    }
}
