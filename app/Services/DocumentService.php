<?php

namespace App\Services;

use App\Models\Document;
use App\Models\RentalShop;
use Illuminate\Database\Eloquent\Collection;

class DocumentService
{
    public function getAll(?string $search): Collection|array
    {
        return Document::query()
            ->when($search, fn($query) => $query->searchName($search))
            ->get();
    }

    public function addRequirement(array $data): Document
    {
        $rentalShop = RentalShop::findOrFail($data['rental_shop_id']);
        $rentalShop->documents()->syncWithoutDetaching([
            $data['document_id'] => [
                'customer_type_id' => $data['customer_type_id']
            ]
        ]);

        return Document::findOrFail($data['document_id']);
    }

    public function addRequirementDocument(array $data): Document
    {
        $rentalShop = RentalShop::findOrFail($data['rental_shop_id']);
        $rentalShop->documents()->syncWithoutDetaching([
            $data['document_id'] => [
                'customer_type_id' => $data['customer_type_id']
            ]
        ]);

        return Document::findOrFail($data['document_id']);
    }
}

