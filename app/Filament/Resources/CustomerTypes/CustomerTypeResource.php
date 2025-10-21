<?php

namespace App\Filament\Resources\CustomerTypes;

use App\Filament\Resources\CustomerTypes\Pages\CreateCustomerType;
use App\Filament\Resources\CustomerTypes\Pages\EditCustomerType;
use App\Filament\Resources\CustomerTypes\Pages\ListCustomerTypes;
use App\Filament\Resources\CustomerTypes\Schemas\CustomerTypeForm;
use App\Filament\Resources\CustomerTypes\Tables\CustomerTypesTable;
use App\Models\CustomerType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerTypeResource extends Resource
{
    protected static ?string $model = CustomerType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.customer_type.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.customer_type.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.inventory'); // group name
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTypesTable::configure($table);
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
            'index' => ListCustomerTypes::route('/'),
            'create' => CreateCustomerType::route('/create'),
            'edit' => EditCustomerType::route('/{record}/edit'),
        ];
    }
}
