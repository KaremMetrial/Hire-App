<?php

use App\Models\Booking;
use App\Models\BookingAccidentReport;
use App\Models\Car;
use App\Models\RentalShop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('user can submit accident report for active booking', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    $accidentData = [
        'booking_id' => $booking->id,
        'accident_location' => 'Main Street, Downtown',
        'accident_details' => 'Minor collision with another vehicle',
        'latitude' => 30.0444,
        'longitude' => 31.2357,
        'severity' => 'minor',
        'date' => now()->format('Y-m-d H:i:s'),
        'images' => [
            UploadedFile::fake()->image('accident1.jpg'),
            UploadedFile::fake()->image('accident2.jpg'),
        ],
    ];

    $response = $this->actingAs($user, 'user')
        ->postJson('/api/v1/accident-reports', $accidentData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'accident_report' => [
                    'id',
                    'booking_id',
                    'accident_location',
                    'accident_details',
                    'status',
                    'severity',
                    'images',
                ],
            ],
            'message',
        ]);

    $this->assertDatabaseHas('booking_accident_reports', [
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'accident_location' => 'Main Street, Downtown',
        'accident_details' => 'Minor collision with another vehicle',
        'severity' => 'minor',
    ]);

    $this->assertDatabaseCount('booking_accident_report_images', 2);
});

test('user cannot submit duplicate pending accident report for same booking', function () {
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    // Create existing pending report
    BookingAccidentReport::factory()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $accidentData = [
        'booking_id' => $booking->id,
        'accident_location' => 'Another Street',
        'accident_details' => 'Another accident',
        'latitude' => 30.0444,
        'longitude' => 31.2357,
        'severity' => 'major',
        'date' => now()->format('Y-m-d H:i:s'),
    ];

    $response = $this->actingAs($user, 'user')
        ->postJson('/api/v1/accident-reports', $accidentData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'You already have a pending accident report for this booking',
        ]);
});

test('user can get their accident reports', function () {
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    $report = BookingAccidentReport::factory()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user, 'user')
        ->getJson('/api/v1/accident-reports');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'accident_reports' => [
                    '*' => [
                        'id',
                        'booking_id',
                        'accident_location',
                        'status',
                        'severity',
                    ],
                ],
            ],
            'message',
        ]);
});

test('user can filter accident reports by status', function () {
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    BookingAccidentReport::factory()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    BookingAccidentReport::factory()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'status' => 'reviewed',
    ]);

    $response = $this->actingAs($user, 'user')
        ->getJson('/api/v1/accident-reports?status=pending');

    $response->assertStatus(200);

    $data = $response->json('data.accident_reports');
    expect($data)->toHaveCount(1);
    expect($data[0]['status'])->toBe('pending');
});

test('user can get specific accident report', function () {
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    $report = BookingAccidentReport::factory()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user, 'user')
        ->getJson("/api/v1/accident-reports/{$report->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'accident_report' => [
                    'id',
                    'booking_id',
                    'accident_location',
                    'accident_details',
                    'status',
                    'severity',
                    'images',
                ],
            ],
            'message',
        ]);
});

test('user cannot access another users accident report', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user1->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    $report = BookingAccidentReport::factory()->create([
        'booking_id' => $booking->id,
        'user_id' => $user1->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user2, 'user')
        ->getJson("/api/v1/accident-reports/{$report->id}");

    $response->assertStatus(404);
});

test('accident report validation works', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'user')
        ->postJson('/api/v1/accident-reports', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'booking_id',
            'accident_location',
            'accident_details',
            'latitude',
            'longitude',
            'severity',
        ]);
});

test('user can submit accident report for any booking status', function () {
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'completed', // Any status should work
    ]);

    $accidentData = [
        'booking_id' => $booking->id,
        'accident_location' => 'Main Street',
        'accident_details' => 'Minor collision',
        'accident_location_coordinates' => [
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ],
        'accident_date' => now()->format('Y-m-d H:i:s'),
        'severity' => 'minor',
        'images' => [
            UploadedFile::fake()->image('accident.jpg'),
        ],
    ];

    $response = $this->actingAs($user, 'user')
        ->postJson('/api/v1/accident-reports', $accidentData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Accident report submitted successfully',
        ]);

    // Verify booking status was updated to accident_reported
    $booking->refresh();
    expect($booking->status)->toBe(\App\Enums\BookingStatusEnum::AccidentReported);
});

test('user cannot submit accident report for booking they do not own', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $rentalShop = RentalShop::factory()->create(['vendor_id' => $vendor->id]);
    $car = Car::factory()->create(['rental_shop_id' => $rentalShop->id]);

    $booking = Booking::factory()->create([
        'user_id' => $user1->id, // Different user
        'car_id' => $car->id,
        'rental_shop_id' => $rentalShop->id,
        'status' => 'active',
    ]);

    $accidentData = [
        'booking_id' => $booking->id,
        'accident_location' => 'Main Street',
        'accident_details' => 'Minor collision',
        'latitude' => 30.0444,
        'longitude' => 31.2357,
        'severity' => 'minor',
        'date' => now()->format('Y-m-d H:i:s'),
    ];

    $response = $this->actingAs($user2, 'user')
        ->postJson('/api/v1/accident-reports', $accidentData);

    $response->assertStatus(404);
});
