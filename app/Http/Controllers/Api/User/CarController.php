<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Http\Resources\PaginationResource;
use App\Models\Car;
use App\Services\CarService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    use ApiResponse;

    public function __construct(protected CarService $carService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'model_id',
            'year_from',
            'year_to',
            'min_price',
            'max_price',
            'fuel_id',
            'transmission_id',
            'category_id',
            'per_page',
            'sort_by',
            'sort_order',
            'rental_shop_id',
            'extra_services_id',
            'rental_type'
        ]);
        $cars = $this->carService->getAll($filters)->load([
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
        ]);
        return $this->successResponse([
            'cars' => CarResource::collection($cars),
            'pagination' => new PaginationResource($cars),
        ], __('message.success'));
    }
    /**
     * Get specific car details
     */
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
            'deliveryOptions',
            'rules'
        );

        return $this->successResponse([
            'car' => new CarResource($car),
        ], __('message.success'));
    }

    public function getByRentalShop(Request $request, int $rentalShopId): JsonResponse
    {
        $filters = $request->validate([
            'model_id' => 'sometimes|integer',
            'color' => 'sometimes|string|max:255',
            'year_from' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'year_to' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'fuel_id' => 'sometimes|integer',
            'transmission_id' => 'sometimes|integer',
            'category_id' => 'sometimes|integer',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'sort_by' => 'sometimes|string|in:newest,latest,oldest,highest_price,price_desc,lowest_price,price_asc',
            'sort_order' => 'sometimes|string|in:asc,desc'
        ]);
        $cars = $this->carService->getByRentalShop($rentalShopId, $filters)->load([
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
        ]);
        return $this->successResponse([
            'cars' => CarResource::collection($cars),
            'pagination' => new PaginationResource($cars),
        ], __('message.success'));
    }
}
