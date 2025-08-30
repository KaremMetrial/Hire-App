<?php

    namespace App\Filament\Resources\Vendors\Tables;

    use App\Enums\VendorStatusEnum;
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\ForceDeleteBulkAction;
    use Filament\Actions\RestoreBulkAction;
    use Filament\Tables\Columns\BadgeColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Filters\TrashedFilter;
    use Filament\Tables\Table;
    use Illuminate\Support\Facades\Storage;
    use Filament\Tables\Columns\ImageColumn;

    class VendorsTable
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
                        ->toggleable(),

                    BadgeColumn::make('status')
                        ->label(__('filament.fields.status'))
                        ->colors([
                            'secondary' => VendorStatusEnum::PENDING->value,
                            'success' => VendorStatusEnum::APPROVED->value,
                            'danger' => VendorStatusEnum::REJECTED->value,
                        ])
                        ->formatStateUsing(fn ($state) => $state?->label())
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

                    ImageColumn::make('national_id_photo')
                        ->label(__('filament.fields.national_id_photo'))
                        ->disk('public')
                        ->url(fn ($record): ?string => $record->national_id_photo ? Storage::url($record->national_id_photo) : null)
                        ->openUrlInNewTab()
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
                            fn($record) => $record->name ?? __('filament.resources.vendor.label')
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
