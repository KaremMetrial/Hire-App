<?php

    namespace App\Filament\Resources\Transmissions\Tables;

    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\DeleteAction;
    use Filament\Tables\Table;
    use Filament\Tables\Columns\TextColumn;
    use Illuminate\Database\Eloquent\Builder;
    use App\Models\Transmissions;

    class TransmissionsTable
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
                ])
                ->filters([
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                        ->recordTitle(fn($record) =>
                            $record->translate(app()->getLocale())->name
                            ?? __('filament.resources.transmission.label')
                        ),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
        }
    }
