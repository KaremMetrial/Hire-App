<?php

namespace App\Listeners;

use App\Contracts\BookingStatusChangeInterface;
use App\Events\BookingStatusChanged;
use App\Notifications\BookingStatusUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class NotifyBookingStatusChange implements BookingStatusChangeInterface
{
    public function handleStatusChange($booking, string $oldStatus, string $newStatus, array $context = []): void
    {
        try {
            // Notify user about status change
            if ($booking->user) {
                $booking->user->notify(new BookingStatusUpdated($booking, $oldStatus, $newStatus));
            }

            // Notify vendor if applicable
            if (isset($context['notify_vendor']) && $context['notify_vendor'] && $booking->rentalShop) {
                // Notify all vendors of the rental shop
                foreach ($booking->rentalShop->vendors as $vendor) {
                    $vendor->notify(new BookingStatusUpdated($booking, $oldStatus, $newStatus, true));
                }
            }

            Log::info('Booking status change notifications sent', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send booking status change notifications', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);

            // Don't re-throw - notification failure shouldn't break the booking process
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
