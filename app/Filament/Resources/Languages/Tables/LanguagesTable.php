<?php

namespace App\Filament\Resources\Languages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Language;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.fields.name'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('native_name')
                    ->label(__('filament.fields.native_name'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('code')
                    ->label(__('filament.fields.code'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('direction')
                    ->label(__('filament.fields.direction'))
                    ->sortable()
                    ->toggleable(),

                ToggleColumn::make('is_default')
                    ->label(__('filament.fields.default'))
                    ->sortable()
                    ->afterStateUpdated(function($record, $state){
                        if($state){
                            Language::where('id', '!=', $record->id)->update(['is_default' => false]);
                        }
                    }),

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

                SelectFilter::make('is_default')
                    ->label(__('filament.filters.default'))
                    ->options([
                        1 => __('filament.labels.default'),
                        0 => __('filament.labels.not_default'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
