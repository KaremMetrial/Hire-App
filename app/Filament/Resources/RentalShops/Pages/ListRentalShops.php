<?php

namespace App\Filament\Resources\RentalShops\Pages;

use App\Filament\Resources\RentalShops\RentalShopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalShops extends ListRecords
{
    protected static string $resource = RentalShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
