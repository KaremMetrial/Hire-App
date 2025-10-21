<?php

namespace App\Repositories\Interfaces;

use App\Models\CustomerType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerTypeRepositoryInterface
{
    public function all(?string $search = null): LengthAwarePaginator;

    public function find(int $id): ?CustomerType;
}
