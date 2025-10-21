<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'national_id_photo' => fake()->imageUrl(640, 480, 'business'),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'actioned_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'rejected_reason' => fake()->optional(0.1)->sentence(),
            'actioned_by' => fake()->optional(0.7)->numberBetween(1, 10),
        ];
    }
}
