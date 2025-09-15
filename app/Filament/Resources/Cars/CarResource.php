<?php

namespace App\Filament\Resources\Cars;

use App\Filament\Resources\Cars\Pages\CreateCar;
use App\Filament\Resources\Cars\Pages\EditCar;
use App\Filament\Resources\Cars\Pages\ListCars;
use App\Filament\Resources\Cars\RelationManagers\CarImagesRelationManager;
use App\Filament\Resources\Cars\Schemas\CarForm;
use App\Filament\Resources\Cars\Tables\CarsTable;
use App\Models\Car;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    public static function getLabel(): string
    {
        return __('filament.resources.car.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.car.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.inventory'); // group name
    }

    public static function form(Schema $schema): Schema
    {
        return CarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarsTable::configure($table);
    }


    public static function getPages(): array
    {
        return [
            'index' => ListCars::route('/'),
            'create' => CreateCar::route('/create'),
            'edit' => EditCar::route('/{record}/edit'),
        ];
    }
}
