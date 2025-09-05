<?php

namespace App\Filament\Resources\CarModels\Schemas;

use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section: Translations
                Section::make(__('filament.sections.translations'))
                    ->schema([
                        TranslatableFields::make([
                            'name' => [
                                'type' => 'text',
                                'label' => __('filament.fields.name'),
                                'overrides' => fn($component) => $component->columnSpanFull(),
                            ],
                        ]),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                // Section: Car Model Info
                Section::make(__('filament.sections.car_model_info'))
                    ->schema([
                        Select::make('brand_id')
                            ->label(__('filament.fields.brand'))
                            ->relationship('brand', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->translate(app()->getLocale())->name ?? $record->id
                            )
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Brand::whereHas(
                                    'translations',
                                    function ($query) use ($search) {
                                        $query->where('locale', app()->getLocale())
                                            ->where('name', 'like', "%{$search}%");
                                    }
                                )
                                    ->with([
                                        'translations' => function ($query) {
                                            $query->where('locale', app()->getLocale());
                                        }
                                    ])
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($brand) {
                                        $name = optional(
                                            $brand->translate(app()->getLocale())
                                        )->name ?? $brand->id;
                                        return [$brand->id => $name];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->preload()
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('filament.fields.is_active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
