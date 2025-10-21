<?php

namespace Database\Seeders;

use App\Enums\CarImageTypeEnum;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Database\Seeder;

class CarImageSeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::all();

        foreach ($cars as $car) {
            // Add a main image
            CarImage::create([
                'car_id' => $car->id,
                'image' => fake()->imageUrl(640, 480, 'cars', true),
                'image_name' => CarImageTypeEnum::FRONT,
            ]);

            // Add a few other images
            for ($i = 0; $i < 3; $i++) {
                CarImage::create([
                    'car_id' => $car->id,
                    'image' => fake()->imageUrl(640, 480, 'cars', true),
                    'image_name' => CarImageTypeEnum::OTHER,
                ]);
            }
        }
    }
}
