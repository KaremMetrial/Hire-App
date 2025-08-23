<?php

namespace App\Filament\Resources\Languages\Schemas;

use App\Models\Language;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.sections.language_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament.fields.name'))
                            ->required()
                            ->maxLength(50)
                            ->columnSpanFull(),

                        TextInput::make('native_name')
                            ->label(__('filament.fields.native_name'))
                            ->required()
                            ->maxLength(50)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label(__('filament.fields.code'))
                            ->required()
                            ->maxLength(10)
                            ->columnSpanFull(),

                        TextInput::make('direction')
                            ->label(__('filament.fields.direction'))
                            ->placeholder('ltr / rtl')
                            ->required()
                            ->maxLength(3)
                            ->columnSpanFull(),

                        Toggle::make('is_default')
                            ->label(__('filament.fields.default'))
                            ->default(false)
                            ->afterStateUpdated(function ($state, $set, $record) {
                                if ($state) {
                                        Language::where('id', '!=', optional($record)->id)
                                        ->update(['is_default' => false]);

                                    $set('is_default', true);
                                }
                            })
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
