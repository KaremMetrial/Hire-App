<?php

namespace App\Console\Commands;

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:check-overdue {--notify : Send notifications for overdue bookings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue bookings and mark them as unreasonable delay';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue bookings...');

        // Find active bookings where return date has passed
        $overdueBookings = Booking::where('status', BookingStatusEnum::Active->value)
            ->where('return_date', '<', now())
            ->with(['user', 'car.rentalShop'])
            ->get();

        if ($overdueBookings->isEmpty()) {
            $this->info('No overdue bookings found.');
            return 0;
        }

        $this->info("Found {$overdueBookings->count()} overdue bookings.");

        foreach ($overdueBookings as $booking) {
            try {
                // Update status to unreasonable delay
                $booking->update(['status' => BookingStatusEnum::UnreasonableDelay->value]);

                $this->line("Marked booking {$booking->id} as unreasonable delay");

                // Log the status change
                Log::warning("Booking {$booking->id} marked as overdue", [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'return_date' => $booking->return_date,
                    'days_overdue' => now()->diffInDays($booking->return_date),
                ]);

                // Send notification if requested
                if ($this->option('notify')) {
                    // Notify user
                    if ($booking->user) {
                        $booking->user->notify(new \App\Notifications\BookingOverdueNotification($booking));
                    }

                    // Notify rental shop vendors
                    if ($booking->rentalShop) {
                        foreach ($booking->rentalShop->vendors as $vendor) {
                            $vendor->notify(new \App\Notifications\BookingOverdueNotification($booking, true));
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error("Failed to process booking {$booking->id}: {$e->getMessage()}");
                Log::error("Failed to mark booking as overdue", [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Overdue booking check completed.');
        return 0;
    }
}
