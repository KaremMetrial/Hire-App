<?php

use App\Models\Bookmark;
use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can get all bookmarks for authenticated user', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);
    $bookmark = Bookmark::factory()->forUser($user)->forCar($car)->create();

    $response = $this->actingAs($user, 'user')
        ->getJson('/api/v1/bookmarks');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'car_id',
                    'car' => [
                        'id',
                        'model',
                        'brand',
                        'year',
                        'color',
                        'license_plate',
                        'num_of_seats',
                        'kilometers',
                        'fuel_type',
                        'transmission_type',
                        'category',
                        'is_active',
                        'bookmark_count',
                        'images',
                        'rental_shop',
                        'city',
                    ],
                    'created_at',
                    'updated_at',
                    'booked_at',
                    'booked_at_formatted',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('can bookmark a car', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user, 'user')
        ->postJson("/api/v1/bookmarks/toggle/{$car->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_bookmarked' => true,
                'car_id' => $car->id,
            ],
        ]);

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $user->id,
        'car_id' => $car->id,
    ]);
});

it('can unbookmark a car', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);
    Bookmark::factory()->forUser($user)->forCar($car)->create();

    $response = $this->actingAs($user, 'user')
        ->postJson("/api/v1/bookmarks/toggle/{$car->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_bookmarked' => false,
                'car_id' => $car->id,
            ],
        ]);

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $user->id,
        'car_id' => $car->id,
    ]);
});

it('cannot bookmark an inactive car', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user, 'user')
        ->postJson("/api/v1/bookmarks/toggle/{$car->id}");

    $response->assertStatus(404);
});

it('can check if a car is bookmarked', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);
    Bookmark::factory()->forUser($user)->forCar($car)->create();

    $response = $this->actingAs($user, 'user')
        ->getJson("/api/v1/bookmarks/check/{$car->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_bookmarked' => true,
                'car_id' => $car->id,
            ],
        ]);
});

it('can check if a car is not bookmarked', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user, 'user')
        ->getJson("/api/v1/bookmarks/check/{$car->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_bookmarked' => false,
                'car_id' => $car->id,
            ],
        ]);
});

it('can remove a bookmark', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);
    Bookmark::factory()->forUser($user)->forCar($car)->create();

    $response = $this->actingAs($user, 'user')
        ->deleteJson("/api/v1/bookmarks/{$car->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'car_id' => $car->id,
            ],
        ]);

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $user->id,
        'car_id' => $car->id,
    ]);
});

it('cannot remove a non-existent bookmark', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user, 'user')
        ->deleteJson("/api/v1/bookmarks/{$car->id}");

    $response->assertStatus(404);
});

it('can get bookmarked cars as car resources', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);
    Bookmark::factory()->forUser($user)->forCar($car)->create();

    $response = $this->actingAs($user, 'user')
        ->getJson('/api/v1/bookmarks/cars');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'year_of_manufacture',
                    'color',
                    'license_plate',
                    'num_of_seat',
                    'kilometers',
                    'is_active',
                    'car_model',
                    'fuel',
                    'transmission',
                    'category',
                    'rental_shop',
                    'city',
                    'images',
                    'prices',
                    'mileages',
                    'availabilities',
                    'insurances',
                    'delivery_options',
                    'services',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('can get bookmark count for a car', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);

    Bookmark::factory()->forUser($user1)->forCar($car)->create();
    Bookmark::factory()->forUser($user2)->forCar($car)->create();

    $response = $this->actingAs($user1, 'user')
        ->getJson("/api/v1/bookmarks/count/{$car->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'car_id' => $car->id,
                'bookmark_count' => 2,
            ],
        ]);
});

it('cannot access bookmark endpoints without authentication', function () {
    $car = Car::factory()->create(['is_active' => true]);

    $this->getJson('/api/v1/bookmarks')->assertStatus(401);
    $this->postJson("/api/v1/bookmarks/toggle/{$car->id}")->assertStatus(401);
    $this->getJson("/api/v1/bookmarks/check/{$car->id}")->assertStatus(401);
    $this->deleteJson("/api/v1/bookmarks/{$car->id}")->assertStatus(401);
    $this->getJson('/api/v1/bookmarks/cars')->assertStatus(401);
    $this->getJson("/api/v1/bookmarks/count/{$car->id}")->assertStatus(401);
});

it('user can only bookmark a car once', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);

    // First bookmark
    $response1 = $this->actingAs($user, 'user')
        ->postJson("/api/v1/bookmarks/toggle/{$car->id}");

    $response1->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_bookmarked' => true,
                'car_id' => $car->id,
            ],
        ]);

    // Second toggle should unbookmark
    $response2 = $this->actingAs($user, 'user')
        ->postJson("/api/v1/bookmarks/toggle/{$car->id}");

    $response2->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_bookmarked' => false,
                'car_id' => $car->id,
            ],
        ]);

    // Should only have one bookmark record
    $this->assertDatabaseCount('bookmarks', 0);
});

it('car resource includes bookmark information for authenticated user', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['is_active' => true]);
    Bookmark::factory()->forUser($user)->forCar($car)->create();

    $response = $this->actingAs($user, 'user')
        ->getJson("/api/v1/cars/{$car->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'car' => [
                    'id',
                    'is_bookmarked',
                    // ... other car fields
                ],
            ],
        ]);

    $response->assertJsonPath('data.car.is_bookmarked', true);
});



it('car list includes bookmark information', function () {
    $user = User::factory()->create();
    $car1 = Car::factory()->create(['is_active' => true]);
    $car2 = Car::factory()->create(['is_active' => true]);

    // User bookmarks only first car
    Bookmark::factory()->forUser($user)->forCar($car1)->create();

    $response = $this->actingAs($user, 'user')
        ->getJson('/api/v1/cars');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'cars' => [
                    '*' => [
                        'id',
                        'is_bookmarked',
                        // ... other car fields
                    ],
                ],
            ],
        ]);

    $cars = $response->json('data.cars');

    // Find the bookmarked car
    $bookmarkedCar = collect($cars)->firstWhere('id', $car1->id);
    $unbookmarkedCar = collect($cars)->firstWhere('id', $car2->id);

    expect($bookmarkedCar['is_bookmarked'])->toBeTrue();
    expect($unbookmarkedCar['is_bookmarked'])->toBeFalse();
});
