<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus,
        public array $context = []
    ) {}
}
