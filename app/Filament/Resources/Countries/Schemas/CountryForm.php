<?php

    namespace App\Filament\Resources\Countries\Schemas;

    use App\Filament\Components\TranslatableFields;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Toggle;
    use Filament\Schemas\Components\Section;
    use Filament\Schemas\Schema;

    class CountryForm
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
                            ])
                        ])
                        ->columns(1)
                        ->columnSpanFull(),

                    Section::make(__('filament.sections.country_info'))
                        ->schema([
                            TextInput::make('code')
                                ->label(__('filament.fields.code'))
                                ->required()
                                ->maxLength(10)
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
