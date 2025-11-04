<?php

namespace App\Repositories;

use App\Models\Car;
use App\Repositories\Interfaces\CarRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarRepository implements CarRepositoryInterface
{
    const PAGINATION_LIMIT = 15;

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Car::with([
            'carModel.translations',
            'carModel.brand.translations',
            'rentalShop:id,name,image,rating,count_rating',
            'rentalShop.workingDays',
            'prices',
            'images',
            'availabilities',
            'deliveryOptions',
            'carModel.brand',
            'fuel',
            'transmission',
            'category',
            'city',
        ])
            ->whereIsActive(true);

        $this->applyCarFilters($query, $filters);

        // Apply location-based sorting if coordinates are provided
        if (isset($filters['lat']) && isset($filters['lng'])) {
            $this->applyLocationSorting($query, $filters['lat'], $filters['lng']);
        } else {
            $this->applyCarSorting($query, $filters);
        }

        return $query->paginate($filters['per_page'] ?? self::PAGINATION_LIMIT);
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
            'rentalShop.address',
            'rentalShop.workingDays',
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

    public function getByRentalShop(int $rentalShopId, array $filters = []): LengthAwarePaginator
    {
        $query = Car::with([
            'carModel.translations',
            'fuel.translations',
            'transmission.translations',
            'rentalShop',
            'images',
            'prices',
            'mileages',
            'availabilities',
            'deliveryOptions',
            'carModel.brand',
            'fuel',
            'transmission',
            'category',
            'rentalShop.vendors',
            'city',
            'insurances',
            'rules',
        ])
            ->where('rental_shop_id', $rentalShopId)
            ->whereIsActive(true);
        $this->applyCarFilters($query, $filters);
        $this->applyCarSorting($query, $filters);

        return $query->paginate($filters['per_page'] ?? self::PAGINATION_LIMIT);
    }

    /**
     * Get standard car relationships for optimal loading
     */
    private function getCarRelations(): array
    {
        return [
            'carModel.translations',
            'fuel.translations',
            'transmission.translations',
            'rentalShop',
            'images',
            'prices',
            'mileages',
            'availabilities',
            'deliveryOptions',
            'carModel.brand',
            'fuel',
            'transmission',
            'category',
            'rentalShop.vendors',
            'city',
            'insurances',
            'rules',
        ];
    }

    /**
     * Apply filters to car query
     */
    private function applyCarFilters(Builder $query, array $filters): void
    {
        $filterMappings = [
            'model_id' => fn($value) => $query->where('model_id', $value),
            'color' => fn($value) => $query->where('color', $value),
            'year_from' => fn($value) => $query->where('year_of_manufacture', '>=', $value),
            'year_to' => fn($value) => $query->where('year_of_manufacture', '<=', $value),
            'fuel_id' => fn($value) => $query->where('fuel_id', $value),
            'transmission_id' => function($value) use ($query) {
                $ids = (array) $value;
                $query->whereIn('transmission_id', $ids);
            },
            'category_id' => fn($value) => $query->where('category_id', $value),
            'city_id' => fn($value) => $query->where('city_id', $value),
            'rental_shop_id' => function($value) use ($query) {
                $ids = (array) $value;
                $query->whereIn('rental_shop_id', $ids);
            },
            'brand_id' => function($value) use ($query) {
                $ids = (array) $value;
                $query->whereHas('carModel', fn($q) => $q->whereIn('brand_id', $ids));
            },
            'extra_services_id' => function($value) use ($query) {
                $ids = (array) $value;
                $query->whereHas('services', fn($q) => $q->whereIn('extra_service_id', $ids));
            },
            'rental_type' => function($value) use ($query) {
                $types = (array) $value;
                $query->whereHas('prices', fn($q) => $q->whereIn('duration_type', $types)->where('is_active', true));
            },
        ];

        foreach ($filterMappings as $filter => $applyFilter) {
            $value = $filters[$filter] ?? null;
            if ($value !== null && $value !== '') {
                $applyFilter($value);
            }
        }

        // Handle price range filter
        $this->applyPriceFilter($query, $filters);
    }

    /**
     * Apply price range filter
     */
    private function applyPriceFilter(Builder $query, array $filters): void
    {
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;

        if ($minPrice !== null || $maxPrice !== null) {
            $now = now();
            $query->whereHas('prices', function ($q) use ($minPrice, $maxPrice, $now) {
                $expr = "CASE WHEN discounted_price IS NOT NULL AND (discount_start_at IS NULL OR discount_start_at <= ?) AND (discount_end_at IS NULL OR discount_end_at >= ?) THEN discounted_price ELSE price END";
                if ($minPrice !== null) {
                    $q->whereRaw("($expr) >= ?", [$now, $now, $minPrice]);
                }
                if ($maxPrice !== null) {
                    $q->whereRaw("($expr) <= ?", [$now, $now, $maxPrice]);
                }
            });
        }
    }

    /**
     * Apply location-based sorting using latitude and longitude
     */
    private function applyLocationSorting(Builder $query, float $lat, float $lng): void
    {
        $query->selectRaw(
            'cars.*, (6371 * acos(cos(radians(?)) * cos(radians(cities.lat)) * cos(radians(cities.lng) - radians(?)) + sin(radians(?)) * sin(radians(cities.lat)))) AS distance',
            [$lat, $lng, $lat]
        )
        ->join('cities', 'cars.city_id', '=', 'cities.id')
        ->orderBy('distance', 'asc');
    }

    /**
     * Apply sorting to car query
     */
    private function applyCarSorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $sortMapping = [
            'newest' => ['created_at', 'desc'],
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'highest_price' => ['price', 'desc'],
            'price_desc' => ['price', 'desc'],
            'lowest_price' => ['price', 'asc'],
            'price_asc' => ['price', 'asc'],
        ];

        if (isset($sortMapping[$sortBy])) {
            [$field, $direction] = $sortMapping[$sortBy];
            $query->orderBy($field, $direction);
        } elseif (in_array($sortBy, ['rating', 'created_at', 'updated_at', 'city_id'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }
    }


}
