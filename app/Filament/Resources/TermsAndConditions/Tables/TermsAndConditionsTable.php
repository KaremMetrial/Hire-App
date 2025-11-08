<?php

namespace App\Filament\Resources\TermsAndConditions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TermsAndConditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament.fields.title'))
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->searchName($search);
                    })
                    ->toggleable(),

                TextColumn::make('version')
                    ->label(__('filament.fields.version'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label(__('filament.fields.active'))
                    ->sortable(),

                ToggleColumn::make('is_required_agreement')
                    ->label(__('filament.fields.is_required_agreement'))
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
                        fn($record) => $record->translate(app()->getLocale())->title ?? __(
                            'filament.resources.terms_and_conditions.label'
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
