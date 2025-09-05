<?php

namespace App\Repositories\Interfaces;

use App\Models\CarModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ModelRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?CarModel;
}
