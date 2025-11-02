<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingLifecycleService;
use Illuminate\Console\Command;

class ProcessBookingLifecycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:process-lifecycle {--booking_id= : Process specific booking} {--batch_size=100 : Number of bookings to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process booking lifecycle for automated status updates and validations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookingId = $this->option('booking_id');
        $batchSize = (int) $this->option('batch_size');

        $lifecycleService = app(BookingLifecycleService::class);

        if ($bookingId) {
            // Process specific booking
            $booking = Booking::find($bookingId);
            if (!$booking) {
                $this->error("Booking with ID {$bookingId} not found.");
                return 1;
            }

            $this->info("Processing booking {$bookingId}...");
            try {
                $lifecycleService->processBookingLifecycle($booking);
                $this->info("Successfully processed booking {$bookingId}");
            } catch (\Exception $e) {
                $this->error("Failed to process booking {$bookingId}: {$e->getMessage()}");
                return 1;
            }
        } else {
            // Process bookings in batches
            $this->info("Processing bookings in batches of {$batchSize}...");

            Booking::chunk($batchSize, function ($bookings) use ($lifecycleService) {
                foreach ($bookings as $booking) {
                    try {
                        $lifecycleService->processBookingLifecycle($booking);
                        $this->line("Processed booking {$booking->id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to process booking {$booking->id}: {$e->getMessage()}");
                    }
                }
            });

            $this->info('Batch processing completed.');
        }

        return 0;
    }
}
