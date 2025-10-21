<?php

namespace App\Enums;

enum DeliveryOptionTypeEnum: string
{
    case OFFICE = 'office';
    case CUSTOM = 'custom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::OFFICE => __('enums.delivery_option_type.office'),
            self::CUSTOM => __('enums.delivery_option_type.custom'),
        };
    }
}
