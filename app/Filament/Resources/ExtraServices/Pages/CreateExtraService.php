<?php

namespace App\Filament\Resources\ExtraServices\Pages;

use App\Filament\Resources\ExtraServices\ExtraServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExtraService extends CreateRecord
{
    protected static string $resource = ExtraServiceResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
