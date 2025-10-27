<?php

namespace Database\Seeders;

use App\Enums\DeliveryOptionTypeEnum;
use App\Models\Car;
use App\Models\DeliveryOption;
use Illuminate\Database\Seeder;

class DeliveryOptionSeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::all();

        foreach ($cars as $car) {
            // All cars have office pickup (free)
            DeliveryOption::create([
                'car_id' => $car->id,
                'type' => DeliveryOptionTypeEnum::OFFICE,
                'is_active' => true,
                'is_default' => true,
                'price' => 0.00,
            ]);

            // Some cars offer custom delivery (especially luxury cars and those in major cities)
            if ($this->shouldHaveCustomDelivery($car)) {
                DeliveryOption::create([
                    'car_id' => $car->id,
                    'type' => DeliveryOptionTypeEnum::CUSTOM,
                    'is_active' => true,
                    'is_default' => false,
                    'price' => $this->getCustomDeliveryPrice($car),
                ]);
            }
        }
    }

    private function shouldHaveCustomDelivery(Car $car): bool
    {
        $categoryName = $car->category->translate('name', 'en');
        $cityName = $car->city->translate('name', 'en');

        // Luxury cars always offer custom delivery
        if ($categoryName === 'Luxury') {
            return true;
        }

        // Sports cars often offer custom delivery
        if ($categoryName === 'Sports') {
            return rand(1, 2) === 1; // 50% chance
        }

        // Major cities more likely to have custom delivery
        $majorCities = ['Riyadh', 'Dubai', 'Jeddah', 'Amman'];
        if (in_array($cityName, $majorCities)) {
            return rand(1, 3) <= 2; // 66% chance
        }

        // Other cities have 33% chance
        return rand(1, 3) === 1;
    }

    private function getCustomDeliveryPrice(Car $car): float
    {
        $categoryName = $car->category->translate('name', 'en');
        $cityName = $car->city->translate('name', 'en');

        // Base pricing by category
        $basePrice = match($categoryName) {
            'Luxury' => 50.00,
            'Sports' => 35.00,
            'SUV' => 25.00,
            'Pickup' => 30.00,
            default => 20.00,
        };

        // City-based adjustments
        $cityMultiplier = match($cityName) {
            'Dubai' => 1.5, // Higher delivery costs in Dubai
            'Riyadh' => 1.2,
            'Jeddah' => 1.1,
            'Amman' => 1.0,
            default => 0.8,
        };

        return round($basePrice * $cityMultiplier, 2);
    }
}
