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
            self::FRONT => __('car_image.front'),
            self::BACK => __('car_image.back'),
            self::LEFT => __('car_image.left'),
            self::RIGHT => __('car_image.right'),
            self::INSIDE => __('car_image.inside'),
            self::OTHER => __('car_image.other'),
        };
    }
}
