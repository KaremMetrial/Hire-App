<?php

namespace App\Filament\Resources\Cars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Car;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand')
                    ->label(__('filament.fields.brand'))
                    ->getStateUsing(fn (Car $record): ?string => $record->carModel?->brand?->name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('carModel.brand', fn(Builder $q) => $q->searchName($search));
                    })
                    ->toggleable(),

                TextColumn::make('model')
                    ->label(__('filament.fields.model'))
                    ->getStateUsing(fn (Car $record): ?string => $record->carModel?->name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('carModel', fn(Builder $q) => $q->searchName($search));
                    })
                    ->toggleable(),

                TextColumn::make('rental_shop')
                    ->label(__('filament.fields.rental_shop'))
                    ->getStateUsing(fn (Car $record): ?string => $record->rentalShop?->name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('rentalShop', fn(Builder $q) => $q->searchName($search));
                    })
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('filament.fields.is_active'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
