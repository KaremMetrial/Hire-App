<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => __('enums.payment_status.unpaid'),
            self::PartiallyPaid => __('enums.payment_status.partially_paid'),
            self::Paid => __('enums.payment_status.paid'),
            self::Refunded => __('enums.payment_status.refunded'),
        };
    }
}
