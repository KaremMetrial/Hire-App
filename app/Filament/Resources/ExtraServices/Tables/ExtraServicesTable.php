<?php

namespace App\Filament\Resources\ExtraServices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;

class ExtraServicesTable
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

                TextColumn::make('description')
                    ->label(__('filament.fields.description'))
                    ->limit(50)
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->searchDescription($search);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label(__('filament.fields.active'))
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->recordTitle(
                        fn ($record) => $record->translate(app()->getLocale())->name
                            ?? __('filament.resources.extra_service.label')
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
