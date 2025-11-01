<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case UnderDelivery = 'under_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case InfoRequested = 'info_requested';
    case AccidentReported = 'accident_reported';
    case ExtensionRequested = 'extension_requested';
    case UnreasonableDelay = 'unreasonable_delay';
    case UnderDispute = 'under_dispute';

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
            self::UnderDelivery => __('enums.booking_status.under_delivery'),
            self::Completed => __('enums.booking_status.completed'),
            self::Cancelled => __('enums.booking_status.cancelled'),
            self::Rejected => __('enums.booking_status.rejected'),
            self::InfoRequested => __('enums.booking_status.info_requested'),
            self::AccidentReported => __('enums.booking_status.accident_reported'),
            self::ExtensionRequested => __('enums.booking_status.extension_requested'),
            self::UnreasonableDelay => __('enums.booking_status.unreasonable_delay'),
            self::UnderDispute => __('enums.booking_status.under_dispute'),
        };
    }
}
