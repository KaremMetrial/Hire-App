<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarPrice;
use Illuminate\Database\Seeder;

class CarPriceSeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::all();

        foreach ($cars as $car) {
            // Base pricing strategy depending on car category and model
            $basePrice = $this->getBasePrice($car);

            // Daily rate
            CarPrice::create([
                'car_id' => $car->id,
                'duration_type' => 'day',
                'price' => $basePrice,
                'is_active' => true,
            ]);

            // Weekly rate (15% discount from daily)
            CarPrice::create([
                'car_id' => $car->id,
                'duration_type' => 'week',
                'price' => $basePrice * 7 * 0.85,
                'is_active' => true,
            ]);

            // Monthly rate (30% discount from daily)
            CarPrice::create([
                'car_id' => $car->id,
                'duration_type' => 'month',
                'price' => $basePrice * 30 * 0.70,
                'is_active' => true,
            ]);

            // Hourly rate (for short rentals)
            CarPrice::create([
                'car_id' => $car->id,
                'duration_type' => 'hour',
                'price' => $basePrice * 0.15,
                'is_active' => true,
            ]);
        }
    }

    private function getBasePrice($car): float
    {
        // Get car model name to determine pricing tier
        $modelName = $car->carModel->translate('name', 'en');
        $categoryName = $car->category->translate('name', 'en');

        // Luxury cars have higher base prices
        if ($categoryName === 'Luxury') {
            return match(true) {
                str_contains($modelName, '5 Series') || str_contains($modelName, 'E-Class') => 250.00,
                str_contains($modelName, '7 Series') || str_contains($modelName, 'S-Class') => 400.00,
                str_contains($modelName, 'GLE') || str_contains($modelName, 'X5') => 300.00,
                default => 200.00,
            };
        }

        // Sports cars
        if ($categoryName === 'Sports') {
            return match(true) {
                str_contains($modelName, 'Mustang') => 180.00,
                str_contains($modelName, 'Corvette') => 250.00,
                str_contains($modelName, 'Ferrari') || str_contains($modelName, 'Lamborghini') => 800.00,
                default => 150.00,
            };
        }

        // SUVs
        if ($categoryName === 'SUV') {
            return match(true) {
                str_contains($modelName, 'Land Cruiser') => 180.00,
                str_contains($modelName, 'Patrol') => 170.00,
                str_contains($modelName, 'Tahoe') || str_contains($modelName, 'Suburban') => 160.00,
                str_contains($modelName, 'Highlander') => 140.00,
                str_contains($modelName, 'Tucson') || str_contains($modelName, 'Sorento') => 90.00,
                str_contains($modelName, 'RAV4') || str_contains($modelName, 'CR-V') => 85.00,
                default => 100.00,
            };
        }

        // Pickup trucks
        if ($categoryName === 'Pickup') {
            return match(true) {
                str_contains($modelName, 'F-150') => 120.00,
                str_contains($modelName, 'Hilux') => 100.00,
                default => 90.00,
            };
        }

        // Sedans (default category)
        return match(true) {
            str_contains($modelName, 'Camry') => 95.00,
            str_contains($modelName, 'Corolla') => 75.00,
            str_contains($modelName, 'Accord') => 90.00,
            str_contains($modelName, 'Civic') => 70.00,
            str_contains($modelName, 'Sentra') => 65.00,
            str_contains($modelName, 'Altima') => 80.00,
            str_contains($modelName, '3 Series') => 150.00,
            str_contains($modelName, 'C-Class') => 140.00,
            str_contains($modelName, 'Elantra') => 60.00,
            str_contains($modelName, 'Sonata') => 70.00,
            str_contains($modelName, 'Rio') => 50.00,
            str_contains($modelName, 'Forte') => 55.00,
            default => 70.00,
        };
    }
}
