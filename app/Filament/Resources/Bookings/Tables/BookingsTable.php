<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->label(__('filament.fields.booking_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label(__('filament.fields.user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('car.carModel.brand.name')
                    ->label(__('filament.fields.brand'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('car.carModel.name')
                    ->label(__('filament.fields.model'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('rentalShop.name')
                    ->label(__('filament.fields.rental_shop'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('pickup_date')
                    ->label(__('filament.fields.pickup_date'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('return_date')
                    ->label(__('filament.fields.return_date'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('filament.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'primary' => 'active',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => __('filament.statuses.'.($state instanceof \App\Enums\BookingStatusEnum ? $state->value : (string)$state)))
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('payment_status')
                    ->label(__('filament.fields.payment_status'))
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partially_paid',
                        'success' => 'paid',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn ($state): string => __('filament.payment_status.'.($state instanceof \App\Enums\PaymentStatusEnum ? $state->value : $state)))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_price')
                    ->label(__('filament.fields.total_price'))
                    ->money('JOD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.fields.status'))
                    ->options([
                        'pending' => __('filament.statuses.pending'),
                        'confirmed' => __('filament.statuses.confirmed'),
                        'active' => __('filament.statuses.active'),
                        'completed' => __('filament.statuses.completed'),
                        'cancelled' => __('filament.statuses.cancelled'),
                        'rejected' => __('filament.statuses.rejected'),
                    ])
                    ->multiple(),

                SelectFilter::make('payment_status')
                    ->label(__('filament.fields.payment_status'))
                    ->options([
                        'unpaid' => __('filament.payment_status.unpaid'),
                        'partially_paid' => __('filament.payment_status.partially_paid'),
                        'paid' => __('filament.payment_status.paid'),
                        'refunded' => __('filament.payment_status.refunded'),
                    ])
                    ->multiple(),

                SelectFilter::make('rental_shop_id')
                    ->label(__('filament.fields.rental_shop'))
                    ->relationship('rentalShop', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('pickup_date')
                    ->form([
                        Forms\Components\DatePicker::make('pickup_from')
                            ->label(__('filament.filters.from_date')),
                        Forms\Components\DatePicker::make('pickup_until')
                            ->label(__('filament.filters.until_date')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['pickup_from'] ?? null, fn ($q) => $q->whereDate('pickup_date', '>=', $data['pickup_from']))
                            ->when($data['pickup_until'] ?? null, fn ($q) => $q->whereDate('pickup_date', '<=', $data['pickup_until']));
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
                    EditAction::make(),
                    DeleteAction::make()
                    ->recordTitle(fn ($record) => $record->booking_number ?? __('filament.resources.booking.label')),
                ])
            ->toolbarActions([
                    BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ]),
                ])
            ->defaultSort('created_at', 'desc');
    }
}
