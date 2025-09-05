<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ModelResource;
class BrandController extends Controller
{
    use ApiResponse;

    public function __construct(protected BrandService $brandService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $brands = $this->brandService->getAll($request->query('search'));

        return $this->successResponse([
            'brands' => BrandResource::collection($brands)
        ], __('message.success'));
    }

    public function show(Brand $brand): JsonResponse
    {
        $brand->load('translations', 'models');

        return $this->successResponse([
            'brand' => new BrandResource($brand),
            'models' => ModelResource::collection($brand->models)
        ], __('message.success'));
    }
}
