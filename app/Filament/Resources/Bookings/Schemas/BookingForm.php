<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.sections.booking_basic_info'))
                    ->schema([
                        TextInput::make('booking_number')
                            ->label(__('filament.fields.booking_number'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),

                        Select::make('user_id')
                            ->label(__('filament.fields.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled()
                            ->columnSpan(1),

                        Select::make('car_id')
                            ->label(__('filament.fields.car'))
                            ->relationship('car', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->carModel->brand->name} {$record->carModel->name} ({$record->year_of_manufacture})"
                            )
                            ->searchable()
                            ->preload()
                            ->preload()
                            ->required()
                            ->disabled()
                            ->columnSpan(1),

                        Select::make('rental_shop_id')
                            ->label(__('filament.fields.rental_shop'))
                            ->relationship('rentalShop', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->columnSpan(1),

                        Select::make('status')
                            ->label(__('filament.fields.status'))
                            ->options([
                                'pending' => __('filament.statuses.pending'),
                                'confirmed' => __('filament.statuses.confirmed'),
                                'active' => __('filament.statuses.active'),
                                'completed' => __('filament.statuses.completed'),
                                'cancelled' => __('filament.statuses.cancelled'),
                                'rejected' => __('filament.statuses.rejected'),
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        Select::make('payment_status')
                            ->label(__('filament.fields.payment_status'))
                            ->options([
                                'unpaid' => __('filament.payment_status.unpaid'),
                                'partially_paid' => __('filament.payment_status.partially_paid'),
                                'paid' => __('filament.payment_status.paid'),
                                'refunded' => __('filament.payment_status.refunded'),
                            ])
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('filament.sections.dates_locations'))
                    ->schema([
                            DateTimePicker::make('pickup_date')
                            ->label(__('filament.fields.pickup_date'))
                            ->required()
                            ->disabled()
                            ->columnSpan(1),

                            DateTimePicker::make('return_date')
                            ->label(__('filament.fields.return_date'))
                            ->required()
                            ->disabled()
                            ->columnSpan(1),

                            Select::make('pickup_location_type')
                            ->label(__('filament.fields.pickup_location_type'))
                            ->options([
                                'office' => __('filament.location_types.office'),
                                'custom' => __('filament.location_types.custom'),
                            ])
                            ->disabled()
                            ->columnSpan(1),

                            Textarea::make('pickup_address')
                            ->label(__('filament.fields.pickup_address'))
                            ->rows(2)
                            ->disabled()
                            ->columnSpan(1),

                            Select::make('return_location_type')
                            ->label(__('filament.fields.return_location_type'))
                            ->options([
                                'office' => __('filament.location_types.office'),
                                'custom' => __('filament.location_types.custom'),
                            ])
                            ->disabled()
                            ->columnSpan(1),

                            Textarea::make('return_address')
                            ->label(__('filament.fields.return_address'))
                            ->rows(2)
                            ->disabled()
                            ->columnSpan(1),
                        ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('filament.sections.costs'))
                    ->schema([
                            TextInput::make('rental_price')
                            ->label(__('filament.fields.rental_price'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('delivery_fee')
                            ->label(__('filament.fields.delivery_fee'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('extra_services_total')
                            ->label(__('filament.fields.extra_services_total'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('insurance_total')
                            ->label(__('filament.fields.insurance_total'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('mileage_fee')
                            ->label(__('filament.fields.mileage_fee'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('tax')
                            ->label(__('filament.fields.tax'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('discount')
                            ->label(__('filament.fields.discount'))
                            ->numeric()
                            ->prefix('JOD')
                            ->columnSpan(1),

                            TextInput::make('total_price')
                            ->label(__('filament.fields.total_price'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),

                            TextInput::make('deposit_amount')
                            ->label(__('filament.fields.deposit_amount'))
                            ->numeric()
                            ->prefix('JOD')
                            ->disabled()
                            ->columnSpan(1),
                        ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(__('filament.sections.mileage_readings'))
                    ->schema([
                            TextInput::make('pickup_mileage')
                            ->label(__('filament.fields.pickup_mileage'))
                            ->numeric()
                            ->suffix('كم')
                            ->columnSpan(1),

                            TextInput::make('return_mileage')
                            ->label(__('filament.fields.return_mileage'))
                            ->numeric()
                            ->suffix('كم')
                            ->columnSpan(1),

                            TextInput::make('actual_mileage_used')
                            ->label(__('filament.fields.actual_mileage_used'))
                            ->numeric()
                            ->suffix('كم')
                            ->disabled()
                            ->columnSpan(1),
                        ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(__('filament.sections.notes'))
                    ->schema([
                            Textarea::make('customer_notes')
                            ->label(__('filament.fields.customer_notes'))
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),

                            Textarea::make('admin_notes')
                            ->label(__('filament.fields.admin_notes'))
                            ->rows(3)
                            ->columnSpanFull(),

                            Textarea::make('cancellation_reason')
                            ->label(__('filament.fields.cancellation_reason'))
                            ->rows(2)
                            ->visible(fn ($get) => $get('status') === 'cancelled')
                            ->columnSpanFull(),

                            Textarea::make('rejection_reason')
                            ->label(__('filament.fields.rejection_reason'))
                            ->rows(2)
                            ->visible(fn ($get) => $get('status') === 'rejected')
                            ->requiredIf('status', 'rejected')
                            ->columnSpanFull(),
                        ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
