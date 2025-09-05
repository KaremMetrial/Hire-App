<?php

namespace App\Services;

use App\Models\Model;
use App\Repositories\Interfaces\ModelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ModelService
{
    public function __construct(protected ModelRepositoryInterface $modelRepository)
    {
    }

    public function getAll(string $search = null): LengthAwarePaginator
    {
        return $this->modelRepository->all($search);
    }

    public function findById(int $id): ?Model
    {
        return $this->modelRepository->find($id);
    }
}
