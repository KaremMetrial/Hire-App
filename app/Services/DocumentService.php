<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;

class DocumentService
{
    public function getAll(?string $search): Collection|array
    {
        return Document::query()
            ->when($search, fn($query) => $query->searchName($search))
            ->get();
    }
}

