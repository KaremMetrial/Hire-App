<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerTypeResource;
use App\Models\CustomerType;
use App\Services\CustomerTypeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerTypeController extends Controller
{
    use ApiResponse;

    public function __construct(protected CustomerTypeService $customerTypeService) {}

    public function index(Request $request): JsonResponse
    {
        $customerTypes = $this->customerTypeService->getAll($request->query('search'));

        return $this->successResponse([
            'customer_types' => CustomerTypeResource::collection($customerTypes),
        ], __('message.success'));
    }

    public function show(CustomerType $customerType): JsonResponse
    {
        $customerType->load('translations');

        return $this->successResponse([
            'customer_type' => new CustomerTypeResource($customerType),
        ], __('message.success'));
    }
}
