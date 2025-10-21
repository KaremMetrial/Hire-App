<?php

namespace App\Filament\Resources\CustomerTypes\Pages;

use App\Filament\Resources\CustomerTypes\CustomerTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerType extends CreateRecord
{
    protected static string $resource = CustomerTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
