<?php

namespace App\Filament\Resources\TermsAndConditions\Schemas;

use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TermsAndConditionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Translations section
                Section::make(__('filament.sections.translations'))
                    ->schema([
                        TranslatableFields::make([
                            'title' => [
                                'type' => 'text',
                                'label' => __('filament.fields.title'),
                                'overrides' => fn($component) => $component->columnSpanFull(),
                            ],
                            'content' => [
                                'type' => 'textarea',
                                'label' => __('filament.fields.content'),
                                'overrides' => fn($component) => $component->rows(12)->columnSpanFull(),
                            ],
                        ])
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make(__('filament.sections.terms_info'))
                    ->schema([
                        TextInput::make('version')
                            ->label(__('filament.fields.version'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('1.0')
                            ->maxLength(10)
                            ->columnSpanFull(),

                        DateTimePicker::make('effective_date')
                            ->label(__('filament.fields.effective_date'))
                            ->placeholder('Leave empty to activate immediately')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label(__('filament.fields.is_active'))
                            ->default(false)
                            ->columnSpanFull(),

                        Toggle::make('is_required_agreement')
                            ->label(__('filament.fields.is_required_agreement'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
