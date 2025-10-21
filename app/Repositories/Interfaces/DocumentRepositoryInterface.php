<?php

namespace App\Repositories\Interfaces;

use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DocumentRepositoryInterface
{
    public function all(?string $search = null): LengthAwarePaginator;

    public function find(int $id): ?Document;
}
