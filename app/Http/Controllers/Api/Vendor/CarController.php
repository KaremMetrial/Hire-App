<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreCarRequest;
use App\Http\Requests\Vendor\UpdateCarRequest;
use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Services\CarService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CarController extends Controller
{
    use ApiResponse;

    public function __construct(protected CarService $carService) {}

    public function index(): JsonResponse
    {
        $vendor = auth()->user();
        $rentalShopIds = $vendor->rentalShops()->pluck('rental_shops.id');

        $cars = Car::whereIn('rental_shop_id', $rentalShopIds)->with([
            'carModel.brand',
            'fuel',
            'transmission',
            'category',
            'rentalShop.vendors',
            'city',
            'images',
            'prices',
            'mileages',
            'availabilities',
            'insurances',
            'rules',
            'deliveryOptions'
        ])->get();

        return $this->successResponse([
            'cars' => CarResource::collection($cars),
        ], __('message.success'));
    }

    public function store(StoreCarRequest $request): JsonResponse
    {
        $vendor = auth()->user();
        $validated = $request->validated();

        // Ensure the rental_shop_id belongs to the vendor
        if (!$vendor->rentalShops()->where('rental_shops.id', $validated['rental_shop_id'])->exists()) {
            return $this->errorResponse(__('message.access_forbidden'), 403);
        }

        $car = $this->carService->store($validated);
        $car->refresh();
        $car->load('carModel', 'fuel', 'transmission', 'category', 'rentalShop.address', 'city', 'images', 'prices', 'mileages', 'availabilities', 'insurances');

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function show(Car $car): JsonResponse
    {
        $vendor = auth()->user();

        // Check if the car belongs to one of the vendor's rental shops
        if (!$vendor->rentalShops()->where('rental_shops.id', $car->rental_shop_id)->exists()) {
            return $this->errorResponse(__('message.access_forbidden'), 403);
        }

        $car->load(['carModel', 'fuel', 'transmission', 'category', 'rentalShop.address', 'city', 'images', 'prices', 'mileages', 'availabilities', 'insurances', 'services', 'deliveryOptions', 'rules']);

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function update(UpdateCarRequest $request, Car $car): JsonResponse
    {
        $vendor = auth()->user();

        // Check if the car belongs to one of the vendor's rental shops
        if (!$vendor->rentalShops()->where('rental_shops.id', $car->rental_shop_id)->exists()) {
            return $this->errorResponse(__('message.access_forbidden'), 403);
        }

        $validated = $request->validated();

        // If rental_shop_id is being updated, ensure it belongs to the vendor
        if (isset($validated['rental_shop_id']) && !$vendor->rentalShops()->where('rental_shops.id', $validated['rental_shop_id'])->exists()) {
            return $this->errorResponse(__('message.access_forbidden'), 403);
        }

        $this->carService->update($validated, $car);

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function destroy(Car $car): JsonResponse
    {
        $vendor = auth()->user();

        // Check if the car belongs to one of the vendor's rental shops
        if (!$vendor->rentalShops()->where('rental_shops.id', $car->rental_shop_id)->exists()) {
            return $this->errorResponse(__('message.access_forbidden'), 403);
        }

        $this->carService->destroy($car);

        return $this->successResponse(null, __('message.success'));
    }
}
