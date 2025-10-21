<?php

namespace App\Repositories;

use App\Models\Document;
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DocumentRepository implements DocumentRepositoryInterface
{
    public function all(?string $search = null): LengthAwarePaginator
    {
        return Document::active()
            ->when($search, fn ($query, $search) => $query->searchName($search))
            ->withTranslation()
            ->latest()
            ->paginate();
    }

    public function find(int $id): ?Document
    {
        return Document::withTranslation()->find($id);
    }
}
