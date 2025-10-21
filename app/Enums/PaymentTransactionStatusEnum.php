<?php

namespace App\Enums;

enum PaymentTransactionStatusEnum: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.payment_status.pending'),
            self::Completed => __('enums.payment_status.completed'),
            self::Failed => __('enums.payment_status.failed'),
            self::Refunded => __('enums.payment_status.refunded'),
        };
    }
}
