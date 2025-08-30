<?php

namespace App\Filament\Resources\Fuels\Pages;

use App\Filament\Resources\Fuels\FuelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFuel extends CreateRecord
{
    protected static string $resource = FuelResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
