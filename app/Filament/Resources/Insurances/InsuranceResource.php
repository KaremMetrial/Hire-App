<?php

namespace App\Filament\Resources\Insurances;

use App\Filament\Resources\Insurances\Pages\CreateInsurance;
use App\Filament\Resources\Insurances\Pages\EditInsurance;
use App\Filament\Resources\Insurances\Pages\ListInsurances;
use App\Filament\Resources\Insurances\Schemas\InsuranceForm;
use App\Filament\Resources\Insurances\Tables\InsurancesTable;
use App\Models\Insurance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
class InsuranceResource extends Resource
{
    protected static ?string $model = Insurance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.insurance.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.insurance.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.contracts'); // group name
    }

    public static function form(Schema $schema): Schema
    {
        return InsuranceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InsurancesTable::configure($table);
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
            'index' => ListInsurances::route('/'),
            'create' => CreateInsurance::route('/create'),
            'edit' => EditInsurance::route('/{record}/edit'),
        ];
    }
}
