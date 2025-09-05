<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\CarModel;
use App\Services\ModelService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    use ApiResponse;

    public function __construct(protected ModelService $modelService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $models = $this->modelService->getAll($request->query('search'));

        return $this->successResponse([
            'models' => ModelResource::collection($models)
        ], __('message.success'));
    }

    public function show(Model $model): JsonResponse
    {
        $model->load('translations');

        return $this->successResponse([
            'model' => new ModelResource($model)
        ], __('message.success'));
    }
}
