<?php

namespace App\Repositories;

use App\Models\RentalShop;
use App\Repositories\Interfaces\RentalShopRepositryInterface;

class RentalShopRepository implements RentalShopRepositryInterface
{
    public function create($data)
    {
        return RentalShop::create($data);
    }

    public function update(RentalShop $rentalShop, $data)
    {
        $rentalShop->update($data);

        return $rentalShop;
    }

    public function findById(int $id): ?RentalShop
    {
        return RentalShop::find($id);
    }
}
