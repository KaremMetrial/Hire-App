<?php

namespace App\Repositories;

use App\Models\Car;
use App\Repositories\Interfaces\CarRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarRepository implements CarRepositoryInterface
{
    public function all(): LengthAwarePaginator
    {
        return Car::with('carModel', 'fuel', 'transmission', 'category', 'rentalShop', 'city', 'images', 'prices', 'mileages', 'availabilities', 'insurances')
            ->latest()
            ->paginate();
    }

    public function store(array $data): Car
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the Car
            $car = Car::create($data);

            // 2. Handle Image Uploads
            if (isset($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $path = Storage::disk('public')->put('cars', $imageData['image']);
                    $car->images()->create([
                        'image' => $path,
                        'image_name' => $imageData['image_name'],
                    ]);
                }
            }

            // 3. Create Car Prices
            if (isset($data['prices'])) {
                $car->prices()->createMany($data['prices']);
            }

            // 4. Create Car Mileage
            if (isset($data['mileages'])) {
                $car->mileages()->create($data['mileages']);
            }

            // 5. Create Car Availabilities
            if (isset($data['availabilities'])) {
                $car->availabilities()->createMany($data['availabilities']);
            }

            // 6. Attach Insurances (Many-to-Many)
            if (isset($data['insurances'])) {
                $car->insurances()->sync(array_column($data['insurances'], 'insurance_id'));
            }

            // 7. Attach Extra Services with pivot data (Many-to-Many)
            if (isset($data['extra_services'])) {
                $extraServices = [];
                foreach ($data['extra_services'] as $service) {
                    $extraServices[$service['extra_service_id']] = ['price' => $service['price']];
                }
                $car->services()->sync($extraServices);
            }

            // 8. Create Custom Extra Services
            if (isset($data['custom_extra_services'])) {
                $car->services()->createMany($data['custom_extra_services']);
            }

            return $car;
        });
    }

    public function update(array $data, Car $car): Car
    {
        // Note: The update logic will also be complex and should be handled in a transaction.
        // This is a placeholder and will need to be implemented fully.
        $car->update($data);
        return $car;
    }

    public function destroy(Car $car): void
    {
        $car->delete();
    }
}
