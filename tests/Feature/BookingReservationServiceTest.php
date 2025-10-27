<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use App\Services\BookingReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BookingReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BookingReservationService::class);
    }

    /** @test */
    public function it_can_create_reservation_for_available_car()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();
        $pickupDate = now()->addDay();
        $returnDate = now()->addDays(3);

        $token = $this->service->createReservation(
            $car->id,
            $pickupDate->toDateTimeString(),
            $returnDate->toDateTimeString(),
            $user->id
        );

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Check if reservation is stored in cache
        $reservation = Cache::get("car_reservation_{$token}");
        $this->assertNotNull($reservation);
        $this->assertEquals($car->id, $reservation['car_id']);
        $this->assertEquals($user->id, $reservation['user_id']);
    }

    /** @test */
    public function it_fails_to_create_reservation_for_unavailable_car()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();
        $pickupDate = now()->addDay();
        $returnDate = now()->addDays(3);

        // Create a conflicting booking first
        $this->createBooking($car, $pickupDate, $returnDate);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Car is not available for the selected dates');

        $this->service->createReservation(
            $car->id,
            $pickupDate->toDateTimeString(),
            $returnDate->toDateTimeString(),
            $user->id
        );
    }

    /** @test */
    public function it_can_confirm_valid_reservation()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();
        $pickupDate = now()->addDay();
        $returnDate = now()->addDays(3);

        $token = $this->service->createReservation(
            $car->id,
            $pickupDate->toDateTimeString(),
            $returnDate->toDateTimeString(),
            $user->id
        );

        $bookingData = [
            'customer_notes' => 'Test booking notes'
        ];

        $booking = $this->service->confirmReservation($token, $bookingData);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'car_id' => $car->id,
            'customer_notes' => 'Test booking notes'
        ]);

        // Check if reservation is removed from cache
        $this->assertNull(Cache::get("car_reservation_{$token}"));
    }

    /** @test */
    public function it_fails_to_confirm_expired_reservation()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();
        $pickupDate = now()->addDay();
        $returnDate = now()->addDays(3);

        $token = $this->service->createReservation(
            $car->id,
            $pickupDate->toDateTimeString(),
            $returnDate->toDateTimeString(),
            $user->id
        );

        // Manually expire the reservation
        Cache::forget("car_reservation_{$token}");

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Reservation expired or not found');

        $this->service->confirmReservation($token, []);
    }

    /** @test */
    public function it_can_cancel_reservation()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();
        $pickupDate = now()->addDay();
        $returnDate = now()->addDays(3);

        $token = $this->service->createReservation(
            $car->id,
            $pickupDate->toDateTimeString(),
            $returnDate->toDateTimeString(),
            $user->id
        );

        $result = $this->service->cancelReservation($token);

        $this->assertTrue($result);
        $this->assertNull(Cache::get("car_reservation_{$token}"));
    }

    /** @test */
    public function it_can_cleanup_expired_reservations()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();

        // Create multiple reservations
        $tokens = [];
        for ($i = 0; $i < 3; $i++) {
            $token = $this->service->createReservation(
                $car->id,
                now()->addDay()->toDateTimeString(),
                now()->addDays(3)->toDateTimeString(),
                $user->id
            );
            $tokens[] = $token;
        }

        // Manually expire one reservation
        Cache::forget("car_reservation_{$tokens[0]}");

        $cleaned = $this->service->cleanupExpiredReservations();

        $this->assertEquals(1, $cleaned);
    }

    private function createBooking(Car $car, $pickupDate, $returnDate)
    {
        return \App\Models\Booking::factory()->create([
            'car_id' => $car->id,
            'pickup_date' => $pickupDate,
            'return_date' => $returnDate,
            'status' => 'confirmed'
        ]);
    }
}
