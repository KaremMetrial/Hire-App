<?php

    namespace App\Filament\Resources\Vendors\RelationManagers;

    use App\Enums\RentalShopStatusEnum;
    use App\Filament\Resources\RentalShops\RentalShopResource;
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\ForceDeleteBulkAction;
    use Filament\Actions\RestoreBulkAction;
    use Filament\Resources\RelationManagers\RelationManager;
    use Filament\Tables\Columns\BadgeColumn;
    use Filament\Tables\Columns\BooleanColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Filters\TrashedFilter;
    use Filament\Tables\Table;


    class RentalShopsRelationManager extends RelationManager
    {
        protected static string $relationship = 'rentalShops';

        protected static ?string $relatedResource = RentalShopResource::class;

        public function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('name')
                        ->label(__('filament.fields.name'))
                        ->sortable()
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make('phone')
                        ->label(__('filament.fields.phone'))
                        ->sortable()
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make('pivot.role')
                        ->label(__('filament.roles.role'))
                        ->badge()
                        ->formatStateUsing(fn(?string $state): string => $state ? __("filament.roles.{$state}") : '')
                        ->color(fn(?string $state): string => match ($state) {
                            'manager' => 'primary',
                            default => 'secondary',
                        }),

                    BadgeColumn::make('status')
                        ->label(__('filament.fields.status'))
                        ->colors([
                            'secondary' => RentalShopStatusEnum::PENDING->value,
                            'success' => RentalShopStatusEnum::APPROVED->value,
                            'danger' => RentalShopStatusEnum::REJECTED->value,
                        ])
                        ->formatStateUsing(fn($state) => $state?->label())
                        ->sortable()
                        ->toggleable(),

                    BooleanColumn::make('is_active')
                        ->label(__('filament.fields.active'))
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('created_at')
                        ->dateTime()
                        ->label(__('filament.fields.created_at'))
                        ->sortable()
                        ->toggleable(),

                ])
                ->headerActions([
//                CreateAction::make(),
                ])
                ->filters([
                    TrashedFilter::make(),
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                        ->recordTitle(
                            fn($record) => $record->name ?? __('filament.resources.rental_shop.label')
                        ),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                        ForceDeleteBulkAction::make(),
                        RestoreBulkAction::make(),
                    ]),
                ]);
        }
    }
