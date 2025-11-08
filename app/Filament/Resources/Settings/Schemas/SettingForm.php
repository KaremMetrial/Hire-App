<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('key')
                    ->label(__('filament.fields.key'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(),

                Forms\Components\KeyValue::make('value')
                    ->label(__('filament.fields.value'))
                    ->keyLabel(__('filament.fields.language_code'))
                    ->valueLabel(__('filament.fields.text'))
                    ->default([])
                    ->columnSpanFull(),
            ]);
    }
}
