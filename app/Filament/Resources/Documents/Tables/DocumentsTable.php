<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\InputTypeEnum;
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

class DocumentsTable
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

                BadgeColumn::make('input_type')
                    ->label(__('filament.fields.input_type'))
                    ->colors([
                        'primary' => InputTypeEnum::TEXT->value,
                        'success' => InputTypeEnum::FILE->value,
                    ])
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->sortable()
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
                    SelectFilter::make('input_type')
                    ->label(__('filament.filters.input_type'))
                    ->options(
                        collect(InputTypeEnum::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),
                ])
            ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                    ->recordTitle(
                        fn ($record) => $record->translate(app()->getLocale())->name ?? __('filament.resources.document.label')
                    ),
                ])
            ->toolbarActions([
                    BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ]),
                ]);
    }
}
