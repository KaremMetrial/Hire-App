<?php

namespace App\Filament\Resources\Transmissions\Pages;

use App\Filament\Resources\Transmissions\TransmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransmission extends EditRecord
{
    protected static string $resource = TransmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
