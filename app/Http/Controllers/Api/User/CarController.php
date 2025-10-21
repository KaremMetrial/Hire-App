<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Http\Resources\PaginationResource;
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
            'pagination' => new PaginationResource($cars),
        ], __('message.success'));
    }

    public function show(Car $car): JsonResponse
    {
        $car->load(
            'carModel',
            'fuel',
            'transmission',
            'category',
            'rentalShop.address',
            'rentalShop.documents',
            'city',
            'images',
            'prices',
            'mileages',
            'availabilities',
            'insurances',
            'services',
            'deliveryOptions'
        );

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }
}
