<?php

namespace App\Services\Vendor;

use App\Enums\RentalShopStatusEnum;
use App\Models\RentalShop;
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

    public function update(RentalShop $rentalShop, array $data): RentalShop
    {
        $data = array_filter($data, fn ($value) => $value !== null);
        $data['status'] = RentalShopStatusEnum::PENDING->value;
        if (isset($data['address']) && is_array($data['address'])) {
            $data['address'] = array_filter($data['address'], fn ($value) => $value !== null);

            if (! empty($data['address'])) {
                $rentalShop->address()->update($data['address']);
            }
        }

        return $this->rentalShopRespoitory->update($rentalShop, $data);
    }
}
