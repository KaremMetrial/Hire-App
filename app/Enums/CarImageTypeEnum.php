<?php

namespace App\Enums;

enum CarImageTypeEnum: string
{
    case FRONT = 'front';
    case BACK = 'back';
    case LEFT = 'left';
    case RIGHT = 'right';
    case INSIDE = 'inside';
    case OTHER = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::FRONT => __('enums.car_image.front'),
            self::BACK => __('enums.car_image.back'),
            self::LEFT => __('enums.car_image.left'),
            self::RIGHT => __('enums.car_image.right'),
            self::INSIDE => __('enums.car_image.inside'),
            self::OTHER => __('enums.car_image.other'),
        };
    }
}
