<?php

    namespace App\Filament\Resources\Brands\Schemas;

    use App\Filament\Components\TranslatableFields;
    use Filament\Forms\Components\FileUpload;
    use Filament\Forms\Components\Toggle;
    use Filament\Schemas\Components\Section;
    use Filament\Schemas\Schema;

    class BrandForm
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

                    // Brand info section
                    Section::make(__('filament.sections.brand_info'))
                        ->schema([
                            FileUpload::make('image')
                                ->label(__('filament.fields.image'))
                                ->image()
                                ->disk('public')
                                ->directory('brands')
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
