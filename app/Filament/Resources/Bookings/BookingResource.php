<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getLabel(): string
    {
        return __('filament.resources.booking.label'); // singular
    }

    public static function getPluralLabel(): string
    {
        return __('filament.resources.booking.plural'); // plural
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.booking_management'); // group name
    }

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\Section::make(__('filament.sections.booking_basic_info'))
                    ->components([
                        Infolists\Components\TextEntry::make('booking_number')
                            ->label(__('filament.fields.booking_number'))
                            ->copyable(),
                        Infolists\Components\TextEntry::make('status')
                            ->label(__('filament.fields.status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'active' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'rejected' => 'danger',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => __('filament.statuses.pending'),
                                'confirmed' => __('filament.statuses.confirmed'),
                                'active' => __('filament.statuses.active'),
                                'completed' => __('filament.statuses.completed'),
                                'cancelled' => __('filament.statuses.cancelled'),
                                'rejected' => __('filament.statuses.rejected'),
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label(__('filament.fields.payment_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'unpaid' => 'danger',
                                'partially_paid' => 'warning',
                                'paid' => 'success',
                                'refunded' => 'secondary',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'unpaid' => __('filament.payment_status.unpaid'),
                                'partially_paid' => __('filament.payment_status.partially_paid'),
                                'paid' => __('filament.payment_status.paid'),
                                'refunded' => __('filament.payment_status.refunded'),
                                default => $state,
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make(__('filament.sections.customer_info'))
                    ->components([
                            Infolists\Components\TextEntry::make('user.name')
                            ->label(__('filament.fields.name')),
                            Infolists\Components\TextEntry::make('user.email')
                            ->label(__('filament.fields.email')),
                            Infolists\Components\TextEntry::make('user.phone')
                            ->label(__('filament.fields.phone')),
                        ])
                    ->columns(3),

                Infolists\Components\Section::make(__('filament.sections.car_info'))
                    ->components([
                            Infolists\Components\TextEntry::make('car.model.brand.name')
                            ->label(__('filament.fields.brand')),
                            Infolists\Components\TextEntry::make('car.model.name')
                            ->label(__('filament.fields.model')),
                            Infolists\Components\TextEntry::make('car.year_of_manufacture')
                            ->label(__('filament.fields.year_of_manufacture')),
                            Infolists\Components\TextEntry::make('rentalShop.name')
                            ->label(__('filament.fields.rental_shop')),
                        ])
                    ->columns(4),

                Infolists\Components\Section::make(__('filament.sections.dates_locations'))
                    ->components([
                            Infolists\Components\TextEntry::make('pickup_date')
                            ->label(__('filament.fields.pickup_date'))
                            ->dateTime('Y-m-d H:i'),
                            Infolists\Components\TextEntry::make('return_date')
                            ->label(__('filament.fields.return_date'))
                            ->dateTime('Y-m-d H:i'),
                            Infolists\Components\TextEntry::make('pickup_location_type')
                            ->label(__('filament.fields.pickup_location_type'))
                            ->formatStateUsing(fn (string $state): string => $state === 'office'
                                ? __('filament.location_types.office_pickup')
                                : __('filament.location_types.custom')),
                            Infolists\Components\TextEntry::make('pickup_address')
                            ->label(__('filament.fields.pickup_address'))
                            ->default(__('filament.defaults.office_pickup')),
                            Infolists\Components\TextEntry::make('return_location_type')
                            ->label(__('filament.fields.return_location_type'))
                            ->formatStateUsing(fn (string $state): string => $state === 'office'
                                ? __('filament.location_types.office_return')
                                : __('filament.location_types.custom')),
                            Infolists\Components\TextEntry::make('return_address')
                            ->label(__('filament.fields.return_address'))
                            ->default(__('filament.defaults.office_return')),
                        ])
                    ->columns(2),

                Infolists\Components\Section::make(__('filament.sections.costs'))
                    ->components([
                            Infolists\Components\TextEntry::make('rental_price')
                            ->label(__('filament.fields.rental_price'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('delivery_fee')
                            ->label(__('filament.fields.delivery_fee'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('extra_services_total')
                            ->label(__('filament.fields.extra_services_total'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('insurance_total')
                            ->label(__('filament.fields.insurance_total'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('mileage_fee')
                            ->label(__('filament.fields.mileage_fee'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('tax')
                            ->label(__('filament.fields.tax'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('discount')
                            ->label(__('filament.fields.discount'))
                            ->money('EGP'),
                            Infolists\Components\TextEntry::make('total_price')
                            ->label(__('filament.fields.total_price'))
                            ->money('EGP')
                            ->weight('bold')
                            ->size('lg'),
                            Infolists\Components\TextEntry::make('deposit_amount')
                            ->label(__('filament.fields.deposit_amount'))
                            ->money('EGP'),
                        ])
                    ->columns(3),

                Infolists\Components\Section::make(__('filament.sections.mileage_readings'))
                    ->components([
                            Infolists\Components\TextEntry::make('pickup_mileage')
                            ->label(__('filament.fields.pickup_mileage'))
                            ->suffix(' '.__('filament.units.km')),
                            Infolists\Components\TextEntry::make('return_mileage')
                            ->label(__('filament.fields.return_mileage'))
                            ->suffix(' '.__('filament.units.km')),
                            Infolists\Components\TextEntry::make('actual_mileage_used')
                            ->label(__('filament.fields.actual_mileage_used'))
                            ->suffix(' '.__('filament.units.km')),
                        ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->pickup_mileage !== null),

                Infolists\Components\Section::make(__('filament.sections.extra_services'))
                    ->components([
                            Infolists\Components\RepeatableEntry::make('extraServices')
                            ->label('')
                            ->components([
                                Infolists\Components\TextEntry::make('extraService.name')
                                    ->label(__('filament.fields.service')),
                                Infolists\Components\TextEntry::make('price')
                                    ->label(__('filament.fields.price'))
                                    ->money('EGP'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label(__('filament.fields.quantity')),
                            ])
                            ->columns(3),
                        ])
                    ->visible(fn ($record) => $record->extraServices->count() > 0),

                Infolists\Components\Section::make(__('filament.sections.insurances'))
                    ->components([
                            Infolists\Components\RepeatableEntry::make('insurances')
                            ->label('')
                            ->components([
                                Infolists\Components\TextEntry::make('insurance.title')
                                    ->label(__('filament.fields.insurance_type')),
                                Infolists\Components\TextEntry::make('price')
                                    ->label(__('filament.fields.price'))
                                    ->money('EGP'),
                                Infolists\Components\TextEntry::make('deposit_price')
                                    ->label(__('filament.fields.deposit_amount'))
                                    ->money('EGP'),
                            ])
                            ->columns(3),
                        ])
                    ->visible(fn ($record) => $record->insurances->count() > 0),

                Infolists\Components\Section::make(__('filament.sections.payments'))
                    ->components([
                            Infolists\Components\RepeatableEntry::make('payments')
                            ->label('')
                            ->components([
                                Infolists\Components\TextEntry::make('amount')
                                    ->label(__('filament.fields.amount'))
                                    ->money('EGP'),
                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label(__('filament.fields.payment_method'))
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'cash' => __('filament.payment_methods.cash'),
                                        'card' => __('filament.payment_methods.card'),
                                        'bank_transfer' => __('filament.payment_methods.bank_transfer'),
                                        'online' => __('filament.payment_methods.online'),
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('payment_type')
                                    ->label(__('filament.fields.payment_type'))
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'rental' => __('filament.payment_types.rental'),
                                        'deposit' => __('filament.payment_types.deposit'),
                                        'extra_fees' => __('filament.payment_types.extra_fees'),
                                        'refund' => __('filament.payment_types.refund'),
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('status')
                                    ->label(__('filament.fields.status'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'secondary',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => __('filament.statuses.pending'),
                                        'completed' => __('filament.statuses.completed'),
                                        'failed' => __('filament.statuses.failed'),
                                        'refunded' => __('filament.payment_status.refunded'),
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('payment_date')
                                    ->label(__('filament.fields.payment_date'))
                                    ->dateTime('Y-m-d H:i'),
                            ])
                            ->columns(5),
                        ])
                    ->visible(fn ($record) => $record->payments->count() > 0),

                Infolists\Components\Section::make(__('filament.sections.notes'))
                    ->components([
                            Infolists\Components\TextEntry::make('customer_notes')
                                ->label(__('filament.fields.customer_notes'))
                                ->columnSpanFull(),
                            Infolists\Components\TextEntry::make('admin_notes')
                                ->label(__('filament.fields.admin_notes'))
                                ->columnSpanFull(),
                            Infolists\Components\TextEntry::make('cancellation_reason')
                                ->label(__('filament.fields.cancellation_reason'))
                                ->columnSpanFull()
                                ->visible(fn ($record) => $record->status === 'cancelled'),
                            Infolists\Components\TextEntry::make('rejection_reason')
                                ->label(__('filament.fields.rejection_reason'))
                                ->columnSpanFull()
                                ->visible(fn ($record) => $record->status === 'rejected'),
                        ])
                    ->columns(1),

                Infolists\Components\Section::make(__('filament.sections.timestamps'))
                    ->components([
                            Infolists\Components\TextEntry::make('created_at')
                                ->label(__('filament.fields.created_at'))
                                ->dateTime('Y-m-d H:i'),
                            Infolists\Components\TextEntry::make('confirmed_at')
                                ->label(__('filament.fields.confirmed_at'))
                                ->dateTime('Y-m-d H:i'),
                            Infolists\Components\TextEntry::make('completed_at')
                                ->label(__('filament.fields.completed_at'))
                                ->dateTime('Y-m-d H:i'),
                            Infolists\Components\TextEntry::make('cancelled_at')
                                ->label(__('filament.fields.cancelled_at'))
                                ->dateTime('Y-m-d H:i')
                                ->visible(fn ($record) => $record->cancelled_at !== null),
                        ])
                    ->columns(4),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
