<?php

namespace App\Filament\Resources\Transmissions;

use App\Filament\Resources\Transmissions\Pages\CreateTransmission;
use App\Filament\Resources\Transmissions\Pages\EditTransmission;
use App\Filament\Resources\Transmissions\Pages\ListTransmissions;
use App\Filament\Resources\Transmissions\Schemas\TransmissionForm;
use App\Filament\Resources\Transmissions\Tables\TransmissionsTable;
use App\Models\Transmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
class TransmissionResource extends Resource
{
    protected static ?string $model = Transmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.transmission.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.transmission.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.settings'); // group name
    }

    public static function form(Schema $schema): Schema
    {
        return TransmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransmissionsTable::configure($table);
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
            'index' => ListTransmissions::route('/'),
            'create' => CreateTransmission::route('/create'),
            'edit' => EditTransmission::route('/{record}/edit'),
        ];
    }
}
