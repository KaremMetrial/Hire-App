<?php

namespace App\Filament\Resources\Transmissions\Pages;

use App\Filament\Resources\Transmissions\TransmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransmissions extends ListRecords
{
    protected static string $resource = TransmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
