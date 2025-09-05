<?php

namespace App\Services;

use App\Models\Fuel;
use App\Repositories\Interfaces\FuelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FuelService
{
    public function __construct(protected FuelRepositoryInterface $fuelRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->fuelRepository->all($search);
    }

    public function findById(int $id): ?Fuel
    {
        return $this->fuelRepository->find($id);
    }
}
