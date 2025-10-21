<?php

namespace App\Services;

use App\Models\CustomerType;
use App\Repositories\Interfaces\CustomerTypeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerTypeService
{
    public function __construct(protected CustomerTypeRepositoryInterface $customerTypeRepository) {}

    public function getAll(?string $search = null): LengthAwarePaginator
    {
        return $this->customerTypeRepository->all($search);
    }

    public function findById(int $id): ?CustomerType
    {
        return $this->customerTypeRepository->find($id);
    }
}
