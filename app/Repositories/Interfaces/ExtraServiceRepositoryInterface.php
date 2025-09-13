<?php

namespace App\Repositories\Interfaces;

use App\Models\ExtraService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ExtraServiceRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?ExtraService;
}
