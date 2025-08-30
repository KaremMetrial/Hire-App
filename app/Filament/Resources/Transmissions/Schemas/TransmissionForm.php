<?php

    namespace App\Filament\Resources\Transmissions\Schemas;

    use App\Filament\Components\TranslatableFields;
    use Filament\Schemas\Components\Section;
    use Filament\Schemas\Schema;

    class TransmissionForm
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
                ]);
        }
    }
