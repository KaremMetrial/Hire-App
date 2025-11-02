<?php

namespace App\Listeners;

use App\Contracts\BookingStatusChangeInterface;
use App\Events\BookingStatusChanged;
use App\Models\BookingStatusLog;
use Illuminate\Support\Facades\Log;

class LogBookingStatusChange implements BookingStatusChangeInterface
{
    public function handleStatusChange($booking, string $oldStatus, string $newStatus, array $context = []): void
    {
        try {
            // Log to database
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by_type' => $context['changed_by_type'] ?? 'system',
                'changed_by_id' => $context['changed_by_id'] ?? null,
                'notes' => $context['notes'] ?? null,
            ]);

            // Log to application log
            Log::info('Booking status changed', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $context['changed_by_type'] ?? 'system',
                'notes' => $context['notes'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log booking status change', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);

            // Don't re-throw - logging failure shouldn't break the booking process
        }
    }

    public function handle(BookingStatusChanged $event): void
    {
        $this->handleStatusChange(
            $event->booking,
            $event->oldStatus,
            $event->newStatus,
            $event->context
        );
    }
}
