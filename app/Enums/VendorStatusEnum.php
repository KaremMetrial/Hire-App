<?php

    namespace App\Enums;

    enum VendorStatusEnum: string
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
                self::PENDING => __('enums.vendor_status.pending'),
                self::REJECTED => __('enums.vendor_status.rejected'),
                self::APPROVED => __('enums.vendor_status.approved'),
            };
        }
    }
