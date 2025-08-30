<?php

    namespace App\Services\Vendor;

    use App\Repositories\Interfaces\RentalShopRepositryInterface;

    class RentalShopService
    {
        protected RentalShopRepositryInterface $rentalShopRespoitory;

        public function __construct(RentalShopRepositryInterface $rentalShopRespoitory)
        {
            $this->rentalShopRespoitory = $rentalShopRespoitory;
        }

        public function create($data)
        {
            $addressData = $data['address'];
            unset($data['address']);

            $rentalShop = $this->rentalShopRespoitory->create($data);

            $rentalShop->address()->create($addressData);

            return $rentalShop;
        }
    }
