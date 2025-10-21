<?php

namespace App\Repositories\Interfaces;

use App\Models\RentalShop;

interface RentalShopRepositryInterface
{
    public function create($data);

    public function update(RentalShop $rentalShop, $data);
}
