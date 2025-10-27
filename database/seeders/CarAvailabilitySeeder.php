<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarAvailability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CarAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::all();

        foreach ($cars as $car) {
            // Most cars are available by default
            CarAvailability::create([
                'car_id' => $car->id,
                'is_available' => true,
                'unavailable_from' => null,
                'unavailable_to' => null,
                'reason' => null,
            ]);

            // Add some scheduled maintenance periods for random cars
            if (rand(1, 3) === 1) { // 33% chance of having maintenance
                $this->createMaintenancePeriod($car);
            }

            // Add some holiday unavailability for luxury cars
            if ($car->category->translate('name', 'en') === 'Luxury' && rand(1, 2) === 1) {
                $this->createHolidayUnavailability($car);
            }
        }
    }

    private function createMaintenancePeriod(Car $car): void
    {
        $startDate = Carbon::now()->addDays(rand(15, 60));
        $duration = rand(2, 5); // 2-5 days maintenance

        CarAvailability::create([
            'car_id' => $car->id,
            'is_available' => false,
            'unavailable_from' => $startDate->toDateString(),
            'unavailable_to' => $startDate->copy()->addDays($duration)->toDateString(),
            'reason' => 'Scheduled maintenance',
        ]);
    }

    private function createHolidayUnavailability(Car $car): void
    {
        // Create unavailability during peak holiday season
        $startDate = Carbon::createFromDate(date('Y'), 12, 20); // December 20th
        $endDate = Carbon::createFromDate(date('Y'), 12, 31); // December 31st

        CarAvailability::create([
            'car_id' => $car->id,
            'is_available' => false,
            'unavailable_from' => $startDate->toDateString(),
            'unavailable_to' => $endDate->toDateString(),
            'reason' => 'Holiday season - owner use',
        ]);
    }
}
