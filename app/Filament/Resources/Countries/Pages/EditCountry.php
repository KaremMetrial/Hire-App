<?php

    namespace App\Filament\Resources\Countries\Pages;

    use App\Filament\Resources\Countries\CountryResource;
    use Filament\Resources\Pages\EditRecord;

    class EditCountry extends EditRecord
    {
        protected static string $resource = CountryResource::class;

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
