<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categoryRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->categoryRepository->all($search);
    }

    public function findById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }
}
