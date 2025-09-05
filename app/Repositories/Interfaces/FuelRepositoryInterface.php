<?php

namespace App\Repositories\Interfaces;

use App\Models\Fuel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FuelRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?Fuel;
}
