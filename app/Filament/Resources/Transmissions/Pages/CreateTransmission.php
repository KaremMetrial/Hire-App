<?php

namespace App\Filament\Resources\Transmissions\Pages;

use App\Filament\Resources\Transmissions\TransmissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransmission extends CreateRecord
{
    protected static string $resource = TransmissionResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
