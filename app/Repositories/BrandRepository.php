<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandRepository implements BrandRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return Brand::active()
            ->when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?Brand
    {
        return Brand::withTranslation()->find($id);
    }
}