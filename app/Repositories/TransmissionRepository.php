<?php

namespace App\Repositories;

use App\Models\Transmission;
use App\Repositories\Interfaces\TransmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransmissionRepository implements TransmissionRepositoryInterface
{
    public function all(string $search = null): LengthAwarePaginator
    {
        return Transmission::when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?Transmission
    {
        return Transmission::withTranslation()->find($id);
    }
}
