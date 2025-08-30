<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Components\TranslatableFields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;

class CategoryForm
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
                        ])
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                // Category info section
                Section::make(__('filament.sections.category_info'))
                    ->schema([
                        FileUpload::make('icon')
                            ->label(__('filament.fields.icon'))
                            ->image()
                            ->required()
                            ->directory('category-icons')
                            ->disk('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
