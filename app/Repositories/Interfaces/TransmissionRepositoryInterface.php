<?php

namespace App\Repositories\Interfaces;

use App\Models\Transmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransmissionRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator;
    public function find(int $id): ?Transmission;
}
