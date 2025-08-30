<?php

    namespace App\Repositories;

    use App\Repositories\Interfaces\RentalShopRepositryInterface;
    use App\Models\RentalShop;

    class RentalShopRepository implements RentalShopRepositryInterface
    {
        public function create($data)
        {
            return RentalShop::create($data);
        }
    }
