<?php

namespace App\Repositories\Interfaces;

use App\Models\Vendor;

interface VendorRepositoryInterface
{
    public function create($data);

    public function findBy(string $field, string $value);

    public function update(Vendor $vendor, array $data);
}
