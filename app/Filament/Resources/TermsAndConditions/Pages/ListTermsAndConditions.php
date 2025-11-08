<?php

namespace App\Filament\Resources\TermsAndConditions\Pages;

use App\Filament\Resources\TermsAndConditions\TermsAndConditionsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTermsAndConditions extends ListRecords
{
    protected static string $resource = TermsAndConditionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
