<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?Category;
}
