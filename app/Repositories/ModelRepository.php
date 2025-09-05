<?php

namespace App\Repositories;

use App\Models\CarModel;
use App\Repositories\Interfaces\ModelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ModelRepository implements ModelRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return CarModel::active()
            ->when($search, fn ($query, $search) => $query->searchName($search))
            ->with(['translations', 'brand'])
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?CarModel
    {
        return CarModel::with(['translations', 'brand'])->find($id);
    }
}
