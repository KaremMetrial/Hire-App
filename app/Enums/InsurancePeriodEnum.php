<?php

namespace App\Enums;

enum InsurancePeriodEnum: string
{
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
            self::DAY => __('enums.insurance_period.day'),
            self::WEEK => __('enums.insurance_period.week'),
            self::MONTH => __('enums.insurance_period.month'),
        };
    }
}
