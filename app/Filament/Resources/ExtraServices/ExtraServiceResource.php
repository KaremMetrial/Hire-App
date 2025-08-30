<?php

namespace App\Filament\Resources\ExtraServices;

use App\Filament\Resources\ExtraServices\Pages\CreateExtraService;
use App\Filament\Resources\ExtraServices\Pages\EditExtraService;
use App\Filament\Resources\ExtraServices\Pages\ListExtraServices;
use App\Filament\Resources\ExtraServices\Schemas\ExtraServiceForm;
use App\Filament\Resources\ExtraServices\Tables\ExtraServicesTable;
use App\Models\ExtraService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
class ExtraServiceResource extends Resource
{
    protected static ?string $model = ExtraService::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.extra_service.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.extra_service.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.settings'); // group name
    }

    protected static ?string $recordTitleAttribute = '.';

    public static function form(Schema $schema): Schema
    {
        return ExtraServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExtraServicesTable::configure($table);
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
            'index' => ListExtraServices::route('/'),
            'create' => CreateExtraService::route('/create'),
            'edit' => EditExtraService::route('/{record}/edit'),
        ];
    }
}
