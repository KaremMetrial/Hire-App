<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Custom booking commands
Artisan::command('bookings:cleanup-reservations', function () {
    $this->info('Cleaning up expired reservations...');
    $service = app(\App\Services\BookingReservationService::class);
    $cleaned = $service->cleanupExpiredReservations();
    $this->info("Cleaned up {$cleaned} expired reservations.");
})->purpose('Clean up expired booking reservations');

Artisan::command('reviews:send-reminders', function () {
    $this->info('Sending review reminders...');
    $service = app(\App\Services\AutoReviewService::class);
    $sent = $service->sendReviewReminders();
    $this->info("Sent {$sent} review reminders.");
})->purpose('Send review reminders to users');

Artisan::command('bookings:check-problematic', function () {
    $this->info('Checking for problematic bookings...');
    $service = app(\App\Services\BookingService::class);
    $problematic = $service->getProblematicBookings();

    if (empty($problematic)) {
        $this->info('No problematic bookings found.');
        return;
    }

    $this->info("Found " . count($problematic) . " problematic bookings:");
    foreach ($problematic as $booking) {
        $this->line("- Booking #{$booking['id']}: {$booking['issue']}");
    }
})->purpose('Check for problematic bookings that need attention');
