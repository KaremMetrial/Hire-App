<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Online = 'online';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('enums.payment_method.cash'),
            self::Card => __('enums.payment_method.card'),
            self::BankTransfer => __('enums.payment_method.bank_transfer'),
            self::Online => __('enums.payment_method.online'),
        };
    }
}
