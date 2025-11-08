<?php

namespace App\Filament\Resources\TermsAndConditions\Pages;

use App\Filament\Resources\TermsAndConditions\TermsAndConditionsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTermsAndConditions extends EditRecord
{
    protected static string $resource = TermsAndConditionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
