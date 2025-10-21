@php
    $stats = [
        [
            'label' => __('filament.widgets.total_bookings'),
            'value' => \App\Models\Booking::count(),
            'icon' => 'heroicon-o-calendar',
            'color' => 'primary'
        ],
        [
            'label' => __('filament.widgets.status_pending'),
            'value' => \App\Models\Booking::where('status', 'pending')->count(),
            'icon' => 'heroicon-o-clock',
            'color' => 'warning'
        ],
        [
            'label' => __('filament.widgets.status_confirmed'),
            'value' => \App\Models\Booking::where('status', 'confirmed')->count(),
            'icon' => 'heroicon-o-check-badge',
            'color' => 'success'
        ],
        [
            'label' => __('filament.widgets.status_active'),
            'value' => \App\Models\Booking::where('status', 'active')->count(),
            'icon' => 'heroicon-o-play',
            'color' => 'info'
        ],
        [
            'label' => __('filament.widgets.status_completed'),
            'value' => \App\Models\Booking::where('status', 'completed')->count(),
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success'
        ],
        [
            'label' => __('filament.widgets.status_cancelled'),
            'value' => \App\Models\Booking::where('status', 'cancelled')->count(),
            'icon' => 'heroicon-o-x-circle',
            'color' => 'danger'
        ],
        [
            'label' => __('filament.widgets.status_rejected'),
            'value' => \App\Models\Booking::where('status', 'rejected')->count(),
            'icon' => 'heroicon-o-no-symbol',
            'color' => 'danger'
        ],
        [
            'label' => __('filament.widgets.total_revenue'),
            'value' => 'EGP ' . number_format(\App\Models\Booking::where('status', 'completed')->sum('total_price'), 2),
            'icon' => 'heroicon-o-currency-dollar',
            'color' => 'success'
        ],
    ];

    $paymentStats = [
        'unpaid' => \App\Models\Booking::where('payment_status', 'unpaid')->count(),
        'partially_paid' => \App\Models\Booking::where('payment_status', 'partially_paid')->count(),
        'paid' => \App\Models\Booking::where('payment_status', 'paid')->count(),
        'refunded' => \App\Models\Booking::where('payment_status', 'refunded')->count(),
    ];

    $topShops = \App\Models\RentalShop::withCount('bookings')
        ->orderBy('bookings_count', 'desc')
        ->limit(5)
        ->get();

    $upcomingBookings = \App\Models\Booking::with(['user', 'car.model.brand', 'rentalShop'])
        ->whereBetween('pickup_date', [now(), now()->addDays(7)])
        ->whereIn('status', ['confirmed', 'active'])
        ->orderBy('pickup_date')
        ->limit(10)
        ->get();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

@foreach($stats as $stat)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <x-filament::icon
                    :icon="$stat['icon']"
                    class="w-8 h-8 text-{{ $stat['color'] }}-500"
                />
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ $stat['label'] }}
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $stat['value'] }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('filament.widgets.payment_status') }}</h3>
            <div class="space-y-3">
                @foreach($paymentStats as $status => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __("filament.widgets.payment_{$status}") }}
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('filament.widgets.top_rental_shops') }}</h3>
            <div class="space-y-3">
                @foreach($topShops as $shop)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $shop->name }}
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ trans_choice('filament.widgets.bookings_count', $shop->bookings_count, ['count' => $shop->bookings_count]) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4">{{ __('filament.widgets.upcoming_bookings') }}</h3>

        @if($upcomingBookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('filament.widgets.booking_number') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('filament.widgets.customer') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('filament.widgets.car') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('filament.widgets.pickup_date') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('filament.widgets.status') }}
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($upcomingBookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $booking->booking_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $booking->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $booking->car->model->brand->name }} {{ $booking->car->model->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $booking->pickup_date->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $booking->status === 'confirmed' ? __('filament.widgets.status_confirmed') : __('filament.widgets.status_active') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                {{ __('filament.widgets.no_upcoming_bookings') }}
            </p>
        @endif
    </div>
    </div>
