<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarMileage;
use Illuminate\Database\Seeder;

class CarMileageSeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::all();

        foreach ($cars as $car) {
            // Different mileage limits based on car category
            $mileageConfig = $this->getMileageConfig($car);

            CarMileage::create([
                'car_id' => $car->id,
                'limit_km_per_day' => $mileageConfig['daily_limit'],
                'limit_km_per_hour' => $mileageConfig['hourly_limit'],
                'extra_fee' => $mileageConfig['extra_fee'],
            ]);
        }
    }

    private function getMileageConfig($car): array
    {
        $categoryName = $car->category->translate('name', 'en');
        $modelName = $car->carModel->translate('name', 'en');

        // Luxury cars have more generous limits but higher extra fees
        if ($categoryName === 'Luxury') {
            return [
                'daily_limit' => 300,
                'hourly_limit' => 50,
                'extra_fee' => 2.50,
            ];
        }

        // Sports cars have moderate limits
        if ($categoryName === 'Sports') {
            return [
                'daily_limit' => 250,
                'hourly_limit' => 40,
                'extra_fee' => 2.00,
            ];
        }

        // SUVs have standard limits
        if ($categoryName === 'SUV') {
            return [
                'daily_limit' => 200,
                'hourly_limit' => 35,
                'extra_fee' => 1.50,
            ];
        }

        // Pickup trucks have higher limits due to work usage
        if ($categoryName === 'Pickup') {
            return [
                'daily_limit' => 250,
                'hourly_limit' => 40,
                'extra_fee' => 1.25,
            ];
        }

        // Sedans (default) - economy focused
        return [
            'daily_limit' => 150,
            'hourly_limit' => 30,
            'extra_fee' => 1.00,
        ];
    }
}
