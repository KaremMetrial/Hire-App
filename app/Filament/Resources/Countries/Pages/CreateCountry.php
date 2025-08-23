<?php

    namespace App\Filament\Resources\Countries\Pages;

    use App\Filament\Resources\Countries\CountryResource;
    use Filament\Resources\Pages\CreateRecord;

    class CreateCountry extends CreateRecord
    {
        protected static string $resource = CountryResource::class;

        protected function getRedirectUrl(): string
        {
            return $this->previousUrl ?? $this->getResource()::getUrl('index');
        }
    }
