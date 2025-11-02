<?php

use App\Models\User;
use App\Models\UserPreRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\{post, assertDatabaseHas, assertDatabaseMissing};

uses(RefreshDatabase::class);

test('can pre-register user with documents', function () {
    Storage::fake('public');

    $data = [
        'name' => 'John Doe',
        'country_id' => 1,
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'birthday' => '1990-01-01',
        'face_license_id_photo' => UploadedFile::fake()->image('face.jpg'),
        'back_license_id_photo' => UploadedFile::fake()->image('back.jpg'),
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ];

    $response = post('/api/v1/pre-register', $data);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'session_token',
                'expires_at',
                'otp_sent',
            ],
            'message'
        ]);

    assertDatabaseHas('user_pre_registrations', [
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
    ]);

    // Check that files were uploaded
    $preRegistration = UserPreRegistration::where('phone', '+1234567890')->first();
    expect($preRegistration->face_license_id_photo)->toBeTruthy();
    expect($preRegistration->back_license_id_photo)->toBeTruthy();
    expect($preRegistration->avatar)->toBeTruthy();
});

test('pre-registration validates required fields', function () {
    $response = post('/api/v1/pre-register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'country_id',
            'phone',
            'email',
            'birthday',
            'face_license_id_photo',
            'back_license_id_photo',
        ]);
});

test('pre-registration validates unique phone and email', function () {
    // Create existing user
    User::factory()->create([
        'phone' => '+1234567890',
        'email' => 'existing@example.com',
    ]);

    $data = [
        'name' => 'John Doe',
        'country_id' => 1,
        'phone' => '+1234567890', // duplicate
        'email' => 'existing@example.com', // duplicate
        'birthday' => '1990-01-01',
        'face_license_id_photo' => UploadedFile::fake()->image('face.jpg'),
        'back_license_id_photo' => UploadedFile::fake()->image('back.jpg'),
    ];

    $response = post('/api/v1/pre-register', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone', 'email']);
});

test('can complete registration after OTP verification', function () {
    Storage::fake('public');

    // Create pre-registration
    $preRegistration = UserPreRegistration::factory()->create([
        'phone' => '+1234567890',
        'expires_at' => now()->addMinutes(30),
    ]);

    // Mock OTP verification (assuming OTP is verified)
    // In real scenario, this would be done through the OTP service

    $data = [
        'identifier' => '+1234567890',
        'otp' => '12345', // Valid OTP
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = post('/api/v1/complete-registration', $data);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'token',
            ],
            'message'
        ]);

    // Check that user was created
    assertDatabaseHas('users', [
        'phone' => '+1234567890',
        'name' => $preRegistration->name,
    ]);

    // Check that pre-registration was deleted
    assertDatabaseMissing('user_pre_registrations', [
        'id' => $preRegistration->id,
    ]);
});

test('complete registration validates password requirements', function () {
    $data = [
        'identifier' => '+1234567890',
        'otp' => '12345',
        'password' => '123', // Too short
        'password_confirmation' => '123',
    ];

    $response = post('/api/v1/complete-registration', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('complete registration requires password confirmation', function () {
    $data = [
        'identifier' => '+1234567890',
        'otp' => '12345',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
    ];

    $response = post('/api/v1/complete-registration', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('pre-registration expires after time limit', function () {
    // Create expired pre-registration
    $preRegistration = UserPreRegistration::factory()->create([
        'phone' => '+1234567890',
        'expires_at' => now()->subMinutes(1), // Already expired
    ]);

    $data = [
        'identifier' => '+1234567890',
        'otp' => '12345',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = post('/api/v1/complete-registration', $data);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => __('message.registration.pre_registration_expired'),
        ]);
});

test('can resend OTP for pre-registration', function () {
    $preRegistration = UserPreRegistration::factory()->create([
        'phone' => '+1234567890',
        'session_token' => 'test-token',
        'expires_at' => now()->addMinutes(30),
    ]);

    $response = post('/api/v1/resend-pre-register-otp', [
        'session_token' => 'test-token',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'session_token',
                'expires_at',
                'otp_sent',
            ],
            'message'
        ]);

    // Check that expiration time was extended
    $preRegistration->refresh();
    expect($preRegistration->expires_at)->toBeGreaterThan(now()->addMinutes(25));
});

test('resend OTP fails for expired pre-registration', function () {
    $preRegistration = UserPreRegistration::factory()->create([
        'phone' => '+1234567890',
        'session_token' => 'test-token',
        'expires_at' => now()->subMinutes(1), // Expired
    ]);

    $response = post('/api/v1/resend-pre-register-otp', [
        'session_token' => 'test-token',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => __('message.registration.pre_registration_expired'),
        ]);
});

test('cleanup command removes expired pre-registrations', function () {
    // Create expired and valid pre-registrations
    UserPreRegistration::factory()->create([
        'expires_at' => now()->subMinutes(1), // Expired
    ]);

    UserPreRegistration::factory()->create([
        'expires_at' => now()->addMinutes(30), // Valid
    ]);

    $expiredCount = UserPreRegistration::where('expires_at', '<=', now())->count();
    expect($expiredCount)->toBe(1);

    // Run cleanup
    UserPreRegistration::cleanupExpired();

    $remainingExpired = UserPreRegistration::where('expires_at', '<=', now())->count();
    expect($remainingExpired)->toBe(0);

    // Valid one should still exist
    $validCount = UserPreRegistration::where('expires_at', '>', now())->count();
    expect($validCount)->toBe(1);
});
