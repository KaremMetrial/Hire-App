<?php

namespace App\Filament\Resources\RentalShops\Pages;

use App\Filament\Resources\RentalShops\RentalShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalShop extends CreateRecord
{
    protected static string $resource = RentalShopResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
