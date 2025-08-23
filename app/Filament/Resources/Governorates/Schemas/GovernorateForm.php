<?php

namespace App\Filament\Resources\Governorates\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class GovernorateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.sections.translations'))
                    ->schema([
                        TranslatableFields::make([
                            'name' => [
                                'type' => 'text',
                                'label' => __('filament.fields.name'),
                                'overrides' => fn ($component) => $component->columnSpanFull(),
                            ],
                        ]),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make(__('filament.sections.governorate_info'))
                    ->schema([
                        Select::make('country_id')
                            ->label(__('filament.fields.country'))
                            ->relationship('country', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->translate(app()->getLocale())->name ?? $record->id)
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Country::whereHas('translations', function ($query) use ($search) {
                                    $query->where('locale', app()->getLocale())
                                        ->where('name', 'like', "%{$search}%");
                                })
                                    ->with(['translations' => function ($query) {
                                        $query->where('locale', app()->getLocale());
                                    }])
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($country) {
                                        $name = optional($country->translate(app()->getLocale()))->name ?? $country->id;
                                        return [$country->id => $name];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->preload()
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
