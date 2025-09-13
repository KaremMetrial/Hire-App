<?php

namespace App\Enums;

enum DeliveryOptionTypeEnum: string
{
    case OFFICE_PICKUP = 'office_pickup';
    case CUSTOMER_DELIVERY = 'customer_delivery';
    case CUSTOMER_PICKUP = 'customer_pickup';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::OFFICE_PICKUP => __('enums.delivery_option_type.office_pickup'),
            self::CUSTOMER_DELIVERY => __('enums.delivery_option_type.customer_delivery'),
            self::CUSTOMER_PICKUP => __('enums.delivery_option_type.customer_pickup'),
        };
    }
}
