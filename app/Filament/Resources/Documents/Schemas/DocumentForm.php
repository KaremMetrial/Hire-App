<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\InputTypeEnum;
use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Translations section
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

                // Document info section
                Section::make(__('filament.sections.document_info'))
                    ->schema([
                        Select::make('input_type')
                            ->label(__('filament.fields.input_type'))
                            ->options(
                                collect(InputTypeEnum::cases())->mapWithKeys(fn ($case) => [
                                    $case->value => $case->label(),
                                ])->toArray()
                            )
                            ->required()
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
