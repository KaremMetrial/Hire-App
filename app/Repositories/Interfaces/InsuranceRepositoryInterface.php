<?php

namespace App\Repositories\Interfaces;

use App\Models\Insurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InsuranceRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?Insurance;
}
