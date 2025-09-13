<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExtraServiceResource;
use App\Models\ExtraService;
use App\Services\ExtraServiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtraServiceController extends Controller
{
    use ApiResponse;

    public function __construct(protected ExtraServiceService $extraServiceService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $extraServices = $this->extraServiceService->getAll($request->query('search'));

        return $this->successResponse([
            'extra_services' => ExtraServiceResource::collection($extraServices)
        ], __('message.success'));
    }

    public function show(ExtraService $extraService): JsonResponse
    {
        $extraService->load('translations');

        return $this->successResponse([
            'extra_service' => new ExtraServiceResource($extraService)
        ], __('message.success'));
    }
}
