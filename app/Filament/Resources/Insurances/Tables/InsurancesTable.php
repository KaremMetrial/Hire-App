<?php

    namespace App\Filament\Resources\Insurances\Tables;

    use App\Enums\InsurancePeriodEnum;
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Tables\Columns\BadgeColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\ToggleColumn;
    use Filament\Tables\Filters\SelectFilter;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Builder;

    class InsurancesTable
    {
        public static function configure(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('title')
                        ->label(__('filament.fields.title'))
                        ->sortable()
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return $query->searchTitle($search);
                        })
                        ->toggleable(),

                    BadgeColumn::make('period')
                        ->label(__('filament.fields.period'))
                        ->colors([
                            'primary' => InsurancePeriodEnum::DAY->value,
                            'info'    => InsurancePeriodEnum::WEEK->value,
                            'success' => InsurancePeriodEnum::MONTH->value,
                        ])
                        ->formatStateUsing(fn ($state) => $state?->label())
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('price')
                        ->label(__('filament.fields.price'))
                        ->money('JOD')
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('deposit_price')
                        ->label(__('filament.fields.deposit_price'))
                        ->money('JOD')
                        ->sortable()
                        ->toggleable(),

                    ToggleColumn::make('is_required')
                        ->label(__('filament.fields.is_required'))
                        ->sortable(),

                    ToggleColumn::make('is_active')
                        ->label(__('filament.fields.active'))
                        ->sortable(),
                ])
                ->filters([
                    SelectFilter::make('is_active')
                        ->label(__('filament.filters.active'))
                        ->options([
                            1 => __('filament.labels.active'),
                            0 => __('filament.labels.inactive'),
                        ]),
                    SelectFilter::make('is_required')
                        ->label(__('filament.filters.required'))
                        ->options([
                            1 => __('filament.labels.required'),
                            0 => __('filament.labels.optional'),
                        ]),
                    SelectFilter::make('period')
                        ->label(__('filament.filters.period'))
                        ->options(
                            collect(InsurancePeriodEnum::cases())
                                ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                                ->toArray()
                        ),
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                        ->recordTitle(
                            fn($record) => $record->translate(app()->getLocale())->title ?? __(
                                'filament.resources.insurance.label'
                            )
                        ),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
        }
    }
