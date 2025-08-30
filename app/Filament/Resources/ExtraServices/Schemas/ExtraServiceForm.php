<?php

namespace App\Filament\Resources\ExtraServices\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;

class ExtraServiceForm
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
                                'overrides' => fn($component) => $component->columnSpanFull(),
                            ],
                            'description' => [
                                'type' => 'textarea',
                                'label' => __('filament.fields.description'),
                                'overrides' => fn($component) => $component->rows(4)->columnSpanFull(),
                            ],
                        ]),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                // Extra Service Info section
                Section::make(__('filament.sections.extra_service_info'))
                    ->schema([
                        FileUpload::make('icon')
                            ->label(__('filament.fields.icon'))
                            ->image()
                            ->directory('extra-services/icons')
                            ->imagePreviewHeight('100')
                            ->maxSize(1024) // 1MB
                            ->disk('public')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label(__('filament.fields.active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
