<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\User\RentalShopResourece;
use App\Models\RentalShop;
use App\Services\User\RentalShopService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalShopController extends Controller
{
    use ApiResponse;

    public function __construct(protected RentalShopService $rentalShopService) {}

    public function index(Request $request): JsonResponse
    {
        $rentalShops = $this->rentalShopService->getAll($request);

        return $this->successResponse([
            'rental_shops' => RentalShopResourece::collection($rentalShops),
            'pagination' => new PaginationResource($rentalShops),
        ], __('message.success'));
    }

    public function show(RentalShop $rentalShop): JsonResponse
    {
        $rentalShop->load([
            'address.country',
            'address.city',
            'workingDays',
            'approvedReviews',
            'allReviews',
            'vendors',
            'documents',
        ]);
        return $this->successResponse([
            'rental_shop' => new RentalShopResourece($rentalShop),
        ], __('message.success'));
    }

    public function getByCity(Request $request, int $cityId): JsonResponse
    {
        $rentalShops = $this->rentalShopService->getByCity($cityId, $request);

        return $this->successResponse([
            'rental_shops' => RentalShopResourece::collection($rentalShops),
            'pagination' => new PaginationResource($rentalShops),
        ], __('message.success'));
    }
}
