<?php

    namespace App\Enums;

    enum DayOfWeekEnum: int
    {
        case MONDAY = 3;
        case TUESDAY = 4;
        case WEDNESDAY = 5;
        case THURSDAY = 6;
        case FRIDAY = 7;
        case SATURDAY = 1;
        case SUNDAY = 2;

        public static function values(): array
        {
            return array_column(self::cases(), 'value');
        }

        public function label(): string
        {
            return match ($this) {
                self::MONDAY => __('days.monday'),
                self::TUESDAY => __('days.tuesday'),
                self::WEDNESDAY => __('days.wednesday'),
                self::THURSDAY => __('days.thursday'),
                self::FRIDAY => __('days.friday'),
                self::SATURDAY => __('days.saturday'),
                self::SUNDAY => __('days.sunday'),
            };
        }
    }
