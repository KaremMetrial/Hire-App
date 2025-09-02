<?php

namespace App\Repositories\Interfaces;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BrandRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?Brand;
}