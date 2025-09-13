<?php

namespace App\Services;

use App\Models\Insurance;
use App\Repositories\Interfaces\InsuranceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InsuranceService
{
    public function __construct(protected InsuranceRepositoryInterface $insuranceRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->insuranceRepository->all($search);
    }

    public function findById(int $id): ?Insurance
    {
        return $this->insuranceRepository->find($id);
    }
}
