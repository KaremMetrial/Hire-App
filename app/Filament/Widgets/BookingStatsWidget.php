<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getHeading(): string
    {
        return __('filament.widgets.booking_stats_heading');
    }

    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $activeBookings = Booking::where('status', 'active')->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $totalRevenue = Booking::where('status', 'completed')->sum('total_price');
        $thisMonthRevenue = Booking::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->sum('total_price');

        return [
            Stat::make(__('filament.widgets.total_bookings'), $totalBookings)
                ->description(__('filament.widgets.all_bookings_in_system'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make(__('filament.widgets.pending_bookings'), $pendingBookings)
                ->description(__('filament.widgets.awaiting_confirmation'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(__('filament.widgets.active_bookings'), $activeBookings)
                ->description(__('filament.widgets.currently_running'))
                ->descriptionIcon('heroicon-o-play')
                ->color('success'),

            Stat::make(__('filament.widgets.completed_bookings'), $completedBookings)
                ->description(__('filament.widgets.successfully_completed'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(__('filament.widgets.total_revenue'), 'EGP '.number_format($totalRevenue, 2))
                ->description(__('filament.widgets.from_completed_bookings'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make(__('filament.widgets.this_month_revenue'), 'EGP '.number_format($thisMonthRevenue, 2))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success'),
        ];
    }
}
