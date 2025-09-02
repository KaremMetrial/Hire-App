<?php

namespace App\Services;

use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandService
{
    public function __construct(protected BrandRepositoryInterface $brandRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->brandRepository->all($search);
    }

    public function findById(int $id): ?Brand
    {
        return $this->brandRepository->find($id);
    }
}