<?php

namespace App\Services;

use App\Models\ExtraService;
use App\Repositories\Interfaces\ExtraServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExtraServiceService
{
    public function __construct(protected ExtraServiceRepositoryInterface $extraServiceRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->extraServiceRepository->all($search);
    }

    public function findById(int $id): ?ExtraService
    {
        return $this->extraServiceRepository->find($id);
    }
}
