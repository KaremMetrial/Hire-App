<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(protected CategoryService $categoryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getAll($request->query('search'));

        return $this->successResponse([
            'categories' => CategoryResource::collection($categories)
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('translations');

        return $this->successResponse([
            'category' => new CategoryResource($category)
        ]);
    }
}
