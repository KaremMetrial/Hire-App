<?php

    namespace App\Filament\Resources\Insurances\Schemas;

    use App\Enums\InsurancePeriodEnum;
    use App\Filament\Components\TranslatableFields;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Toggle;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
    use Filament\Schemas\Components\Section;
    use Filament\Schemas\Schema;

    class InsuranceForm
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
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('filament.fields.description'),
                                    'overrides' => fn($component) => $component->rows(3)->columnSpanFull(),
                                ],
                            ])
                        ])
                        ->columns(1)
                        ->columnSpanFull(),

                    // Insurance info section
                    Section::make(__('filament.sections.insurance_info'))
                        ->schema([
                            Select::make('period')
                                ->label(__('filament.fields.period'))
                                ->options(
                                    collect(InsurancePeriodEnum::cases())
                                        ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                                        ->toArray()
                                )
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('price')
                                ->label(__('filament.fields.price'))
                                ->numeric()
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('deposit_price')
                                ->label(__('filament.fields.deposit_price'))
                                ->numeric()
                                ->required()
                                ->columnSpan(1),

                            Toggle::make('is_required')
                                ->label(__('filament.fields.is_required'))
                                ->default(false)
                                ->columnSpan(1),

                            Toggle::make('is_active')
                                ->label(__('filament.fields.active'))
                                ->default(true)
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]);
        }
    }
