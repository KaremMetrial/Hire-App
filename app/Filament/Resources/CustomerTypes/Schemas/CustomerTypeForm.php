<?php

namespace App\Filament\Resources\CustomerTypes\Schemas;

use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerTypeForm
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

                // CustomerType info section
                Section::make(__('filament.sections.customer_type_info'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('filament.fields.is_active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
