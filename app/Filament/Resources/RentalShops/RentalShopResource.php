<?php

namespace App\Filament\Resources\RentalShops;

use App\Filament\Resources\RentalShops\Pages\CreateRentalShop;
use App\Filament\Resources\RentalShops\Pages\EditRentalShop;
use App\Filament\Resources\RentalShops\Pages\ListRentalShops;
use App\Filament\Resources\RentalShops\Schemas\RentalShopForm;
use App\Filament\Resources\RentalShops\Tables\RentalShopsTable;
use App\Models\RentalShop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
class RentalShopResource extends Resource
{
    protected static ?string $model = RentalShop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.rental_shop.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.rental_shop.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.inventory'); // group name
    }

    public static function form(Schema $schema): Schema
    {
        return RentalShopForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalShopsTable::configure($table);
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
            'index' => ListRentalShops::route('/'),
            'create' => CreateRentalShop::route('/create'),
            'edit' => EditRentalShop::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
