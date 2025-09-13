<?php

namespace App\Repositories;

use App\Models\Insurance;
use App\Repositories\Interfaces\InsuranceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InsuranceRepository implements InsuranceRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return Insurance::active()
            ->when($search, fn ($query, $search) => $query->searchTitle($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?Insurance
    {
        return Insurance::withTranslation()->find($id);
    }
}
