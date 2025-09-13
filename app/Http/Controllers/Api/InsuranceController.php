<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InsuranceResource;
use App\Models\Insurance;
use App\Services\InsuranceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    use ApiResponse;

    public function __construct(protected InsuranceService $insuranceService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $insurances = $this->insuranceService->getAll($request->query('search'));

        return $this->successResponse([
            'insurances' => InsuranceResource::collection($insurances)
        ], __('message.success'));
    }

    public function show(Insurance $insurance): JsonResponse
    {
        $insurance->load('translations');

        return $this->successResponse([
            'insurance' => new InsuranceResource($insurance)
        ], __('message.success'));
    }
}
