<?php

    namespace App\Filament\Resources\Admins\Tables;

    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class AdminsTable
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

                    TextColumn::make('email')
                        ->label(__('filament.fields.email'))
                        ->sortable()
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make('phone')
                        ->label(__('filament.fields.phone'))
                        ->sortable()
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make('created_at')
                        ->label(__('filament.fields.created_at'))
                        ->dateTime()
                        ->sortable()
                        ->toggleable(),

                ])
                ->filters([
                    //
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                    ->visible(fn ($record) => $record->email !== auth('admin')->user()->email),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
        }
    }
