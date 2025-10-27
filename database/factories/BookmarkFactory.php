<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookmark>
 */
class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'car_id' => Car::factory(),
        ];
    }

    /**
     * Create a bookmark for a specific user.
     */
    public function forUser(User|int $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }

    /**
     * Create a bookmark for a specific car.
     */
    public function forCar(Car|int $car): static
    {
        return $this->state(fn (array $attributes) => [
            'car_id' => $car instanceof Car ? $car->id : $car,
        ]);
    }

    /**
     * Create a bookmark for a specific user and car.
     */
    public function forUserAndCar(User|int $user, Car|int $car): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof User ? $user->id : $user,
            'car_id' => $car instanceof Car ? $car->id : $car,
        ]);
    }
}
