<?php

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use App\Models\Vendor;
use App\Models\RentalShop;
use App\Models\CarModel;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Fuel;
use App\Models\Transmission;
use App\Services\BookingService;
use App\Repositories\BookingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{get, post, put, delete, actingAs};

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test data
    $this->brand = Brand::factory()->create();
    $this->category = Category::factory()->create();
    $this->fuel = Fuel::factory()->create();
    $this->transmission = Transmission::factory()->create();
    $this->carModel = CarModel::factory()->create([
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
    ]);

    $this->vendor = Vendor::factory()->create();
    $this->rentalShop = RentalShop::factory()->create();
    $this->vendor->rentalShops()->attach($this->rentalShop->id, ['role' => 'manager']);

    $this->car = Car::factory()->create([
        'model_id' => $this->carModel->id,
        'fuel_id' => $this->fuel->id,
        'transmission_id' => $this->transmission->id,
        'category_id' => $this->category->id,
        'rental_shop_id' => $this->rentalShop->id,
    ]);

    $this->user = User::factory()->create();

    $this->bookingService = app(BookingService::class);
});

test('can calculate booking price', function () {
    $data = [
        'car_id' => $this->car->id,
        'pickup_date' => now()->addDay()->toDateTimeString(),
        'return_date' => now()->addDays(3)->toDateTimeString(),
        'pickup_location_type' => 'office',
        'return_location_type' => 'office',
    ];

    $priceDetails = $this->bookingService->calculatePrice($data);

    expect($priceDetails)->toHaveKeys([
        'rental_days',
        'rental_hours',
        'rental_price',
        'delivery_fee',
        'extra_services_total',
        'insurance_total',
        'tax_amount',
        'total_price',
        'currency'
    ]);

    expect($priceDetails['rental_days'])->toBe(2);
    expect($priceDetails['currency'])->toBe('JOD');
    expect($priceDetails['total_price'])->toBeGreaterThan(0);
});

test('can create booking', function () {
    $data = [
        'car_id' => $this->car->id,
        'pickup_date' => now()->addDay()->toDateTimeString(),
        'return_date' => now()->addDays(3)->toDateTimeString(),
        'pickup_location_type' => 'office',
        'return_location_type' => 'office',
        'customer_notes' => 'Test booking notes',
    ];

    $booking = $this->bookingService->createBooking($data, $this->user->id);

    expect($booking)->toBeInstanceOf(Booking::class);
    expect($booking->user_id)->toBe($this->user->id);
    expect($booking->car_id)->toBe($this->car->id);
    expect($booking->status)->toBe(BookingStatusEnum::Pending->value);
    expect($booking->booking_number)->toStartWith('BK');
    expect($booking->customer_notes)->toBe('Test booking notes');
});

test('cannot create booking for unavailable car', function () {
    // Create an existing booking that conflicts
    $existingBooking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'status' => BookingStatusEnum::Confirmed->value,
        'pickup_date' => now()->addDay(),
        'return_date' => now()->addDays(3),
    ]);

    $data = [
        'car_id' => $this->car->id,
        'pickup_date' => now()->addDay()->toDateTimeString(),
        'return_date' => now()->addDays(3)->toDateTimeString(),
        'pickup_location_type' => 'office',
        'return_location_type' => 'office',
    ];

    expect(fn() => $this->bookingService->createBooking($data, $this->user->id))
        ->toThrow('Car is not available for the selected dates');
});

test('can confirm booking as vendor', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Pending->value,
    ]);

    $confirmedBooking = $this->bookingService->confirmBooking($booking->id, $this->vendor->id);

    expect($confirmedBooking->status)->toBe(BookingStatusEnum::Confirmed->value);
    expect($confirmedBooking->confirmed_at)->not->toBeNull();
});

test('can reject booking as vendor', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Pending->value,
    ]);

    $reason = 'Car not available';
    $rejectedBooking = $this->bookingService->rejectBooking($booking->id, $reason, $this->vendor->id);

    expect($rejectedBooking->status)->toBe(BookingStatusEnum::Rejected->value);
    expect($rejectedBooking->rejection_reason)->toBe($reason);
});

test('can start booking as vendor', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Confirmed->value,
        'pickup_date' => now()->subHour(),
    ]);

    $pickupMileage = 15000;
    $startedBooking = $this->bookingService->startBooking($booking->id, $pickupMileage);

    expect($startedBooking->status)->toBe(BookingStatusEnum::Active->value);
    expect($startedBooking->pickup_mileage)->toBe($pickupMileage);
});

test('can complete booking as vendor', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Active->value,
        'pickup_mileage' => 15000,
    ]);

    $returnMileage = 15200;
    $completedBooking = $this->bookingService->completeBooking($booking->id, $returnMileage);

    expect($completedBooking->status)->toBe(BookingStatusEnum::Completed->value);
    expect($completedBooking->return_mileage)->toBe($returnMileage);
    expect($completedBooking->actual_mileage_used)->toBe(200);
    expect($completedBooking->completed_at)->not->toBeNull();
});

test('can cancel booking as user', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Pending->value,
        'total_price' => 100,
    ]);

    $reason = 'Changed plans';
    $cancelledBooking = $this->bookingService->cancelBooking($booking->id, $this->user->id, $reason);

    expect($cancelledBooking->status)->toBe(BookingStatusEnum::Cancelled->value);
    expect($cancelledBooking->cancellation_reason)->toBe($reason);
    expect($cancelledBooking->cancelled_at)->not->toBeNull();
});

test('cannot cancel completed booking', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Completed->value,
    ]);

    expect(fn() => $this->bookingService->cancelBooking($booking->id, $this->user->id))
        ->toThrow('This booking cannot be cancelled');
});

test('can get user bookings', function () {
    // Create multiple bookings for the user
    Booking::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'car_id' => $this->car->id,
        'rental_shop_id' => $this->rentalShop->id,
    ]);

    $bookings = $this->bookingService->getUserBookings($this->user->id);

    expect($bookings)->toHaveCount(3);
    expect($bookings->first()->user_id)->toBe($this->user->id);
});

test('can get vendor bookings', function () {
    // Create multiple bookings for the vendor's rental shop
    Booking::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'car_id' => $this->car->id,
        'rental_shop_id' => $this->rentalShop->id,
    ]);

    $bookings = $this->bookingService->getVendorBookings($this->vendor->id);

    expect($bookings)->toHaveCount(3);
});

test('can get booking statistics', function () {
    // Create bookings with different statuses
    Booking::factory()->create([
        'user_id' => $this->user->id,
        'car_id' => $this->car->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Pending->value,
    ]);

    Booking::factory()->create([
        'user_id' => $this->user->id,
        'car_id' => $this->car->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Completed->value,
        'total_price' => 200,
    ]);

    $stats = $this->bookingService->getBookingStats();

    expect($stats['total'])->toBe(2);
    expect($stats['pending'])->toBe(1);
    expect($stats['completed'])->toBe(1);
    expect($stats['total_revenue'])->toBe(200);
});

test('can get user booking statistics', function () {
    Booking::factory()->create([
        'user_id' => $this->user->id,
        'car_id' => $this->car->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Completed->value,
        'total_price' => 150,
    ]);

    $stats = $this->bookingService->getUserBookingStats($this->user->id);

    expect($stats['total'])->toBe(1);
    expect($stats['completed'])->toBe(1);
    expect($stats['total_spent'])->toBe(150);
});

test('user booking API endpoints', function () {
    // Test price calculation
    $priceData = [
        'car_id' => $this->car->id,
        'pickup_date' => now()->addDay()->toDateTimeString(),
        'return_date' => now()->addDays(2)->toDateTimeString(),
        'pickup_location_type' => 'office',
        'return_location_type' => 'office',
    ];

    post('/api/v1/bookings/calculate-price', $priceData)
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'price_details' => [
                    'rental_days',
                    'total_price',
                    'currency'
                ]
            ]
        ]);

    // Test booking creation
    $bookingData = [
        'car_id' => $this->car->id,
        'pickup_date' => now()->addDay()->toDateTimeString(),
        'return_date' => now()->addDays(2)->toDateTimeString(),
        'pickup_location_type' => 'office',
        'return_location_type' => 'office',
        'customer_notes' => 'API test booking',
    ];

    actingAs($this->user, 'user')
        ->post('/api/v1/bookings', $bookingData)
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'booking' => [
                    'id',
                    'booking_number',
                    'status'
                ]
            ]
        ]);

    // Test getting user bookings
    actingAs($this->user, 'user')
        ->get('/api/v1/bookings')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'bookings',
                'pagination'
            ]
        ]);
});

test('vendor booking API endpoints', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Pending->value,
    ]);

    // Test getting vendor bookings
    actingAs($this->vendor, 'vendor')
        ->get('/vendor/v1/bookings')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'bookings',
                'pagination'
            ]
        ]);

    // Test confirming booking
    actingAs($this->vendor, 'vendor')
        ->post("/vendor/v1/bookings/{$booking->id}/confirm")
        ->assertStatus(200)
        ->assertJsonPath('data.booking.status', BookingStatusEnum::Confirmed->value);

    // Test rejecting booking
    $booking->update(['status' => BookingStatusEnum::Pending->value]);
    actingAs($this->vendor, 'vendor')
        ->post("/vendor/v1/bookings/{$booking->id}/reject", [
            'rejection_reason' => 'Test rejection'
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.booking.status', BookingStatusEnum::Rejected->value);
});

test('booking status transitions are logged', function () {
    $booking = Booking::factory()->create([
        'car_id' => $this->car->id,
        'user_id' => $this->user->id,
        'rental_shop_id' => $this->rentalShop->id,
        'status' => BookingStatusEnum::Pending->value,
    ]);

    // Confirm booking
    $this->bookingService->confirmBooking($booking->id, $this->vendor->id);

    // Check status log
    $logs = $booking->statusLogs()->get();
    expect($logs)->toHaveCount(2); // Initial + confirmation
    expect($logs->last()->new_status)->toBe(BookingStatusEnum::Confirmed->value);
    expect($logs->last()->changed_by_type)->toBe('vendor');
});

test('booking price calculation with extra services and insurance', function () {
    // Add extra services and insurance to the car
    $this->car->services()->attach(1, ['price' => 10]);
    $this->car->insurances()->attach(1, ['price' => 5]);

    $data = [
        'car_id' => $this->car->id,
        'pickup_date' => now()->addDay()->toDateTimeString(),
        'return_date' => now()->addDays(2)->toDateTimeString(),
        'pickup_location_type' => 'office',
        'return_location_type' => 'office',
        'extra_services' => [
            ['id' => 1, 'quantity' => 2],
        ],
        'insurance_id' => 1,
    ];

    $priceDetails = $this->bookingService->calculatePrice($data);

    expect($priceDetails['extra_services_total'])->toBe(20); // 2 days * 2 quantity * 10 price
    expect($priceDetails['insurance_total'])->toBe(10); // 2 days * 5 price
    expect($priceDetails['total_price'])->toBeGreaterThan(0);
});
