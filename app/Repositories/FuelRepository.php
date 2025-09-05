<?php

namespace App\Repositories;

use App\Models\Fuel;
use App\Repositories\Interfaces\FuelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FuelRepository implements FuelRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return Fuel::when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?Fuel
    {
        return Fuel::withTranslation()->find($id);
    }
}
