<?php

namespace App\Filament\Resources\TermsAndConditions;

use App\Filament\Resources\TermsAndConditions\Pages\CreateTermsAndConditions;
use App\Filament\Resources\TermsAndConditions\Pages\EditTermsAndConditions;
use App\Filament\Resources\TermsAndConditions\Pages\ListTermsAndConditions;
use App\Filament\Resources\TermsAndConditions\Schemas\TermsAndConditionsForm;
use App\Filament\Resources\TermsAndConditions\Tables\TermsAndConditionsTable;
use App\Models\TermsAndConditions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TermsAndConditionsResource extends Resource
{
    protected static ?string $model = TermsAndConditions::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.terms_and_conditions.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.terms_and_conditions.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.settings'); // group name
    }

    protected static ?string $recordTitleAttribute = null;

    public static function form(Schema $schema): Schema
    {
        return TermsAndConditionsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TermsAndConditionsTable::configure($table);
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
            'index' => ListTermsAndConditions::route('/'),
            'create' => CreateTermsAndConditions::route('/create'),
            'edit' => EditTermsAndConditions::route('/{record}/edit'),
        ];
    }
}
