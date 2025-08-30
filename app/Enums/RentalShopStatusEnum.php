<?php

    namespace App\Enums;

    enum RentalShopStatusEnum: string
    {
        case PENDING = 'pending';
        case APPROVED = 'approved';
        case REJECTED = 'rejected';

        public static function values(): array
        {
            return array_column(self::cases(), 'value');
        }

        public function label(): string
        {
            return match ($this) {
                self::PENDING => __('enums.rental_shop_status.pending'),
                self::REJECTED => __('enums.rental_shop_status.rejected'),
                self::APPROVED => __('enums.rental_shop_status.approved'),
            };
        }

    }
