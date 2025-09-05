<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return Category::when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?Category
    {
        return Category::withTranslation()->find($id);
    }
}
