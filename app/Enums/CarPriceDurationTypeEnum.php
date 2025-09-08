<?php

namespace App\Enums;

enum CarPriceDurationTypeEnum: string
{

    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DAY => __('car_price_duration_type.day'),
            self::WEEK => __('car_price_duration_type.week'),
            self::MONTH => __('car_price_duration_type.month'),
            self::HOUR => __('car_price_duration_type.hour'),
        };
    }
}
