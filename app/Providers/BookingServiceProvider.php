<?php

namespace App\Providers;

use App\Services\AutoReviewService;
use App\Services\BookingReservationService;
use App\Services\MileageValidationService;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BookingReservationService::class, function ($app) {
            return new BookingReservationService();
        });

        $this->app->singleton(MileageValidationService::class, function ($app) {
            return new MileageValidationService();
        });

        $this->app->singleton(AutoReviewService::class, function ($app) {
            return new AutoReviewService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Schedule cleanup tasks
        $this->app->booted(function () {
            if (class_exists(\Illuminate\Console\Scheduling\Schedule::class)) {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

                // Clean up expired reservations every hour
                $schedule->call(function () {
                    app(BookingReservationService::class)->cleanupExpiredReservations();
                })->hourly();

                // Send review reminders daily at 9 AM
                $schedule->call(function () {
                    app(AutoReviewService::class)->sendReviewReminders();
                })->dailyAt('09:00');
            }
        });
    }
}
