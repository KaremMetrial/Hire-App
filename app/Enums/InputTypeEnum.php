<?php

namespace App\Enums;

enum InputTypeEnum: string
{
    case TEXT = 'text';
    case FILE = 'file';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::TEXT => __('enums.input_type.text'),
            self::FILE => __('enums.input_type.file'),
        };
    }
}
