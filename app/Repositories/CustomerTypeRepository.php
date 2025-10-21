<?php

namespace App\Repositories;

use App\Models\CustomerType;
use App\Repositories\Interfaces\CustomerTypeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerTypeRepository implements CustomerTypeRepositoryInterface
{
    public function all(?string $search = null): LengthAwarePaginator
    {
        return CustomerType::when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?CustomerType
    {
        return CustomerType::withTranslation()->find($id);
    }
}
