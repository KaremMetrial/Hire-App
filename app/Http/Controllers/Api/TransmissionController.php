<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransmissionResource;
use App\Models\Transmission;
use App\Services\TransmissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransmissionController extends Controller
{
    use ApiResponse;

    public function __construct(protected TransmissionService $transmissionService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $transmissions = $this->transmissionService->getAll($request->query('search'));

        return $this->successResponse([
            'transmissions' => TransmissionResource::collection($transmissions)
        ]);
    }

    public function show(Transmission $transmission): JsonResponse
    {
        $transmission->load('translations');

        return $this->successResponse([
            'transmission' => new TransmissionResource($transmission)
        ]);
    }
}
