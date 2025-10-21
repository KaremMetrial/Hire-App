<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryInterface;

class VendorRepository implements VendorRepositoryInterface
{
    public function create($data)
    {
        return Vendor::create($data);
    }

    public function findBy(string $field, string $value)
    {
        return Vendor::where($field, $value)->first();
    }

    public function update(Vendor $vendor, array $data)
    {
        return $vendor->update($data);
    }
}
