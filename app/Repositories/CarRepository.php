<?php

namespace App\Repositories;

use App\Models\Car;
use App\Repositories\Interfaces\CarRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarRepository implements CarRepositoryInterface
{
    const PAGINATION_LIMIT = 15;

    public function all(): LengthAwarePaginator
    {
        $query = Car::with(
            'carModel',
            'fuel',
            'transmission',
            'category',
            'rentalShop',
            'city',
            'images',
            'prices',
            'mileages',
            'availabilities',
            'insurances',
            'deliveryOptions'
        )
            ->whereIsActive(true);

        if (request()->filled('city_id')) {
            $query->where('city_id', request('city_id'));
        }

        if (request()->filled('model_id')) {
            $query->where('model_id', request('model_id'));
        }

        if (request()->filled('color')) {
            $query->where('color', request('color'));
        }

        if (request()->filled('year_from')) {
            $query->where('year_of_manufacture', '>=', request('year_from'));
        }

        if (request()->filled('year_to')) {
            $query->where('year_of_manufacture', '<=', request('year_to'));
        }

        if (request()->filled('min_price') || request()->filled('max_price')) {
            $query->whereHas('prices', function ($q) {
                if (request()->filled('min_price')) {
                    $q->where('price', '>=', request('min_price'));
                }
                if (request()->filled('max_price')) {
                    $q->where('price', '<=', request('max_price'));
                }
            });
        }

        return $query->latest()->paginate(self::PAGINATION_LIMIT);
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

            // 9. Create Delivery Options
            if (isset($data['delivery_options'])) {
                $car->deliveryOptions()->createMany($data['delivery_options']);
            }

            return $car;
        });
    }

    public function update(array $data, Car $car): Car
    {
        return DB::transaction(function () use ($data, $car) {
            // 1. Update the Car's direct attributes
            $car->update($data);

            // 2. Sync Images
            if (isset($data['images'])) {
                // Delete old images from storage
                foreach ($car->images as $oldImage) {
                    if ($oldImage->image && Storage::disk('public')->exists($oldImage->image)) {
                        Storage::disk('public')->delete($oldImage->image);
                    }
                }
                // Delete old image records
                $car->images()->delete();

                foreach ($data['images'] as $imageData) {
                    $path = null;

                    // Case 1: new uploaded file
                    if (isset($imageData['image']) && $imageData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = Storage::disk('public')->put('cars', $imageData['image']);
                    } // Case 2: existing path (string)
                    elseif (isset($imageData['image']) && is_string($imageData['image'])) {
                        $path = $imageData['image']; // keep the old path
                    }

                    if ($path) {
                        $car->images()->create([
                            'image' => $path,
                            'image_name' => $imageData['image_name'] ?? null,
                        ]);
                    }
                }
            }

            // 3. Sync Prices
            if (isset($data['prices'])) {
                $car->prices()->delete();
                $car->prices()->createMany($data['prices']);
            }

            // 4. Sync Mileage
            if (isset($data['mileages'])) {
                $car->mileages()->delete();
                $car->mileages()->create($data['mileages']);
            }

            // 5. Sync Availabilities
            if (isset($data['availabilities'])) {
                $car->availabilities()->delete();

                $normalized = collect($data['availabilities'])->map(function ($item) {
                    return [
                        'is_available' => $item['is_available'] ?? true, // default true
                        'unavailable_from' => $item['unavailable_from'] ?? null,
                        'unavailable_to' => $item['unavailable_to'] ?? null,
                        'reason' => $item['reason'] ?? null,
                    ];
                })->toArray();

                $car->availabilities()->createMany($normalized);
            }

            // 6. Sync Insurances
            if (isset($data['insurances'])) {
                $car->insurances()->sync(array_column($data['insurances'], 'insurance_id'));
            }

            // 7. Sync Extra Services
            if (isset($data['extra_services'])) {
                $extraServices = [];
                foreach ($data['extra_services'] as $service) {
                    $extraServices[$service['extra_service_id']] = ['price' => $service['price']];
                }
                $car->services()->sync($extraServices);
            } else {
                $car->services()->detach();
            }

            // 8. Sync Custom Extra Services
            if (isset($data['custom_extra_services'])) {
                $car->services()->delete();
                $car->services()->createMany($data['custom_extra_services']);
            }

            // 9. Sync Delivery Options
            if (isset($data['delivery_options'])) {
                $car->deliveryOptions()->delete();
                $car->deliveryOptions()->createMany($data['delivery_options']);
            }

            // 10. Update Rental Shop Rule
            if (isset($data['rental_shop_rule'])) {
                $car->update(['rental_shop_rule' => $data['rental_shop_rule']]);
            }

            return $car->fresh();
        });
    }

    public function destroy(Car $car): void
    {
        $car->delete();
    }

    public function findById(int $id): ?Car
    {
        return Car::with([
            'carModel',
            'fuel',
            'transmission',
            'category',
            'rentalShop',
            'city',
            'images',
            'prices',
            'mileages',
            'availabilities',
            'insurances',
            'deliveryOptions',
            'services'
        ])->find($id);
    }
}
