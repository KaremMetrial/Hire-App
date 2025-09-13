<?php

namespace App\Repositories;

use App\Models\ExtraService;
use App\Repositories\Interfaces\ExtraServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExtraServiceRepository implements ExtraServiceRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return ExtraService::active()
            ->when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?ExtraService
    {
        return ExtraService::withTranslation()->find($id);
    }
}
