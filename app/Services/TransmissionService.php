<?php

namespace App\Services;

use App\Models\Transmission;
use App\Repositories\Interfaces\TransmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransmissionService
{
    public function __construct(protected TransmissionRepositoryInterface $transmissionRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->transmissionRepository->all($search);
    }

    public function findById(int $id): ?Transmission
    {
        return $this->transmissionRepository->find($id);
    }
}
