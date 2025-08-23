<?php

    namespace App\Filament\Resources\Countries\Tables;

    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\ToggleColumn;
    use Filament\Tables\Filters\SelectFilter;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Builder;

    class CountriesTable
    {
        public static function configure(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('name')
                        ->label(__('filament.fields.name'))
                        ->sortable()
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return $query->searchName($search);
                        })
                        ->toggleable(),

                    TextColumn::make('code')
                        ->label(__('filament.fields.code'))
                        ->sortable()
                        ->searchable()
                        ->toggleable(),

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
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                        ->recordTitle(
                            fn($record) => $record->translate(app()->getLocale())->name ?? __(
                                'filament.resources.country.label'
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
