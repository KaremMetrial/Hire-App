<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case InfoRequested = 'info_requested';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.booking_status.pending'),
            self::Confirmed => __('enums.booking_status.confirmed'),
            self::Active => __('enums.booking_status.active'),
            self::Completed => __('enums.booking_status.completed'),
            self::Cancelled => __('enums.booking_status.cancelled'),
            self::Rejected => __('enums.booking_status.rejected'),
            self::InfoRequested => __('enums.booking_status.info_requested'),
        };
    }
}
