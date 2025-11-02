<?php

namespace App\Observers;

use App\Events\BookingStatusChanged;
use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "updating" event.
     */
    public function updating(Booking $booking): void
    {
        // Observer is now simplified - status change events are dispatched directly from services
        // This prevents double dispatching and context issues
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Observer is now simplified - status change events are dispatched directly from services
        // This prevents double dispatching and context issues
    }
}
