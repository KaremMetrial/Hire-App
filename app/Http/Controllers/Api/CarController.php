<?php

namespace App\Http\Controllers\Api;

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
        $cars = $this->carService->getAll();

        return $this->successResponse([
            'cars' => CarResource::collection($cars),
        ], __('message.success'));
    }

    public function store(StoreCarRequest $request): JsonResponse
    {
        $car = $this->carService->store($request->validated());
        $car->refresh();
        $car->load('carModel', 'fuel', 'transmission', 'category', 'rentalShop.address', 'city', 'images', 'prices', 'mileages', 'availabilities', 'insurances');

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function show(Car $car): JsonResponse
    {
        $car->load('carModel', 'fuel', 'transmission', 'category', 'rentalShop.address', 'city', 'images', 'prices', 'mileages', 'availabilities', 'insurances', 'services', 'deliveryOptions');

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function update(UpdateCarRequest $request, Car $car): JsonResponse
    {
        $this->carService->update($request->validated(), $car);

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function destroy(Car $car): JsonResponse
    {
        $this->carService->destroy($car);

        return $this->successResponse(null, __('message.success'));
    }
}
