<?php

    namespace App\Filament\Resources\Countries;

    use App\Filament\Resources\Countries\Pages\CreateCountry;
    use App\Filament\Resources\Countries\Pages\EditCountry;
    use App\Filament\Resources\Countries\Pages\ListCountries;
    use App\Filament\Resources\Countries\Schemas\CountryForm;
    use App\Filament\Resources\Countries\Tables\CountriesTable;
    use App\Models\Country;
    use BackedEnum;
    use Filament\Resources\Resource;
    use Filament\Schemas\Schema;
    use Filament\Support\Icons\Heroicon;
    use Filament\Tables\Table;
    use UnitEnum;


    class CountryResource extends Resource
    {
        protected static ?string $model = Country::class;

        protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;
        protected static string|UnitEnum|null $navigationGroup = null;

        public static function getLabel(): string
        {
            return __('filament.resources.country.label'); // singular
        }

        public static function getPluralLabel(): string
        {
            return __('filament.resources.country.plural'); // plural
        }

        public static function getNavigationGroup(): ?string
        {
            return __('filament.navigation.settings'); // group name
        }


        public static function form(Schema $schema): Schema
        {
            return CountryForm::configure($schema);
        }

        public static function table(Table $table): Table
        {
            return CountriesTable::configure($table);
        }

        public static function getRelations(): array
        {
            return [
                //
            ];
        }

        public static function getPages(): array
        {
            return [
                'index' => ListCountries::route('/'),
                'create' => CreateCountry::route('/create'),
                'edit' => EditCountry::route('/{record}/edit'),
            ];
        }
    }
