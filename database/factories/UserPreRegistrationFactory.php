<?php

namespace Database\Factories;

use App\Models\UserPreRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreRegistration>
 */
class UserPreRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'country_id' => 1, // Use existing country ID
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'birthday' => $this->faker->date('Y-m-d', '-18 years'),
            'face_license_id_photo' => $this->faker->imageUrl(),
            'back_license_id_photo' => $this->faker->imageUrl(),
            'avatar' => $this->faker->optional()->imageUrl(),
            'session_token' => UserPreRegistration::generateSessionToken(),
            'expires_at' => now()->addMinutes(30),
        ];
    }

    /**
     * Indicate that the pre-registration is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(1),
        ]);
    }
}
