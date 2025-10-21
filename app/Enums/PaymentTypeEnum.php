<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case Rental = 'rental';
    case Deposit = 'deposit';
    case ExtraFees = 'extra_fees';
    case Refund = 'refund';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Rental => __('enums.payment_type.rental'),
            self::Deposit => __('enums.payment_type.deposit'),
            self::ExtraFees => __('enums.payment_type.extra_fees'),
            self::Refund => __('enums.payment_type.refund'),
        };
    }
}
