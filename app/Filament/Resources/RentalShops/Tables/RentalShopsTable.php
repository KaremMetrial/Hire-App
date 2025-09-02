<?php

    namespace App\Filament\Resources\RentalShops\Tables;

    use App\Enums\RentalShopStatusEnum;
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\ForceDeleteBulkAction;
    use Filament\Actions\RestoreBulkAction;
    use Filament\Tables\Columns\BadgeColumn;
    use Filament\Tables\Columns\BooleanColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\ImageColumn;
    use Filament\Tables\Filters\TrashedFilter;
    use Filament\Tables\Table;
    use Illuminate\Support\Facades\Storage;

    class RentalShopsTable
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

                    TextColumn::make('phone')
                        ->label(__('filament.fields.phone'))
                        ->sortable()
                        ->searchable()
                        ->toggleable(),

                    BadgeColumn::make('status')
                        ->label(__('filament.fields.status'))
                        ->colors([
                            'secondary' => RentalShopStatusEnum::PENDING->value,
                            'success'   => RentalShopStatusEnum::APPROVED->value,
                            'danger'    => RentalShopStatusEnum::REJECTED->value,
                        ])
                        ->formatStateUsing(fn ($state) => $state?->label())
                        ->sortable()
                        ->toggleable(),

                    BooleanColumn::make('is_active')
                        ->label(__('filament.fields.active'))
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('actioned_at')
                        ->label(__('filament.fields.actioned_at'))
                        ->dateTime()
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('rejected_reason')
                        ->label(__('filament.fields.rejected_reason'))
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('actioned_by')
                        ->label(__('filament.fields.actioned_by'))
                        ->sortable()
                        ->toggleable(),

                    ImageColumn::make('image')
                        ->label(__('filament.fields.image'))
                        ->disk('public')
                        ->url(fn ($record): ?string => $record->image ? Storage::url($record->image) : null)
                        ->openUrlInNewTab()
                        ->sortable()
                        ->toggleable(),

                    ImageColumn::make('transport_license_photo')
                        ->label(__('filament.fields.transport_license_photo'))
                        ->disk('public')
                        ->url(fn ($record): ?string => $record->transport_license_photo ? Storage::url($record->transport_license_photo) : null)
                        ->openUrlInNewTab()
                        ->sortable()
                        ->toggleable(),

                    ImageColumn::make('commerical_registration_photo')
                        ->label(__('filament.fields.commerical_registration_photo'))
                        ->disk('public')
                        ->url(fn ($record): ?string => $record->commerical_registration_photo ? Storage::url($record->commerical_registration_photo) : null)
                        ->openUrlInNewTab()
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('rating')
                        ->label(__('filament.fields.rating'))
                        ->sortable()
                        ->toggleable(),

                    TextColumn::make('count_rating')
                        ->label(__('filament.fields.count_rating'))
                        ->sortable()
                        ->toggleable(),
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
