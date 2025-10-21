<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Models\CarModel;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('model_id')
                    ->label(__('filament.fields.model'))
                    ->options(
                        CarModel::with('brand', 'translations')->get()->mapWithKeys(function ($model) {
                            return [$model->id => ($model->brand->name ?? '').' - '.($model->name ?? '')];
                        })
                    )
                    ->searchable()
                    ->required(),
                Select::make('rental_shop_id')
                    ->label(__('filament.fields.rental_shop'))
                    ->relationship('rentalShop', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('city_id')
                    ->label(__('filament.fields.city'))
                    ->relationship('city', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->translate(app()->getLocale())->name ?? $record->id
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\City::query()
                            ->searchName($search)
                            ->limit(50)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('category_id')
                    ->label(__('filament.fields.category'))
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->translate(app()->getLocale())->name ?? $record->id
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Category::query()
                            ->searchName($search)
                            ->limit(50)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('fuel_id')
                    ->label(__('filament.fields.fuel'))
                    ->relationship('fuel', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->translate(app()->getLocale())->name ?? $record->id
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Fuel::query()
                            ->searchName($search)
                            ->limit(50)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('transmission_id')
                    ->label(__('filament.fields.transmission'))
                    ->relationship('transmission', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->translate(app()->getLocale())->name ?? $record->id
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Transmission::query()
                            ->searchName($search)
                            ->limit(50)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('year_of_manufacture')
                    ->label(__('filament.fields.year_of_manufacture'))
                    ->required(),
                TextInput::make('color')
                    ->label(__('filament.fields.color'))
                    ->required(),
                TextInput::make('license_plate')
                    ->label(__('filament.fields.license_plate'))
                    ->columnSpanFull(),
                TextInput::make('num_of_seat')
                    ->label(__('filament.fields.num_of_seat'))
                    ->numeric()
                    ->required(),
                TextInput::make('kilometers')
                    ->label(__('filament.fields.kilometers'))
                    ->numeric()
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('filament.fields.is_active'))
                    ->default(true)
                    ->columnSpanFull(),
                Repeater::make('images')
                    ->label(__('filament.fields.images'))
                    ->relationship()
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('filament.fields.image'))
                            ->image()
                            ->disk('public')
                            ->directory('cars')
                            ->required(),
                    ])
                    ->columnSpanFull(),
                Repeater::make('prices')
                    ->relationship()
                    ->label(__('filament.fields.prices'))
                    ->schema([
                        Select::make('duration_type')
                            ->label(__('filament.fields.duration_type'))
                            ->options(\App\Enums\CarPriceDurationTypeEnum::class)
                            ->required(),

                        TextInput::make('price')
                            ->label(__('filament.fields.price'))
                            ->numeric()
                            ->required(),

                        Toggle::make('is_active')
                            ->label(__('filament.fields.is_active'))
                            ->default(true),
                    ])
                    ->columnSpanFull(),

            ]);
    }
}
