<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //            Action::make('statistics')
            //                ->label('الإحصائيات')
            //                ->icon('heroicon-o-chart-bar')
            //                ->modalHeading('إحصائيات الحجوزات')
            //                ->modalContent(fn () => view('filament.pages.booking-statistics'))
            //                ->modalWidth('7xl'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => $this->getModel()::count()),

            'pending' => Tab::make('قيد الانتظار')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => $this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'confirmed' => Tab::make('مؤكد')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed'))
                ->badge(fn () => $this->getModel()::where('status', 'confirmed')->count())
                ->badgeColor('success'),

            'active' => Tab::make('نشط')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => $this->getModel()::where('status', 'active')->count())
                ->badgeColor('primary'),

            'completed' => Tab::make('مكتمل')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => $this->getModel()::where('status', 'completed')->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('ملغي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled'))
                ->badge(fn () => $this->getModel()::where('status', 'cancelled')->count())
                ->badgeColor('danger'),
        ];
    }
}
