<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FuelResource;
use App\Models\Fuel;
use App\Services\FuelService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    use ApiResponse;

    public function __construct(protected FuelService $fuelService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $fuels = $this->fuelService->getAll($request->query('search'));

        return $this->successResponse([
            'fuels' => FuelResource::collection($fuels)
        ], __('message.success'));
    }

    public function show(Fuel $fuel): JsonResponse
    {
        $fuel->load('translations');

        return $this->successResponse([
            'fuel' => new FuelResource($fuel)
        ], __('message.success'));
    }
}
