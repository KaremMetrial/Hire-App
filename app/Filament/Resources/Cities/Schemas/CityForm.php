<?php

    namespace App\Filament\Resources\Cities\Schemas;

    use App\Filament\Components\TranslatableFields;
    use Filament\Forms\Components\Select;
    use Filament\Schemas\Components\Section;
    use Filament\Schemas\Schema;

    class CityForm
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

                    // Section: City Info
                    Section::make(__('filament.sections.city_info'))
                        ->schema([
                            Select::make('governorate_id')
                                ->label(__('filament.fields.governorate'))
                                ->relationship('governorate', 'id')
                                ->getOptionLabelFromRecordUsing(
                                    fn($record) => $record->translate(app()->getLocale())->name ?? $record->id
                                )
                                ->getSearchResultsUsing(function (string $search) {
                                    return \App\Models\Governorate::query()
                                        ->searchName($search)
                                        ->limit(50)
                                        ->get()
                                        ->pluck('name', 'id');
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
