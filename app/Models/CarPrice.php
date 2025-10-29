<?php

namespace App\Models;

use App\Enums\CarPriceDurationTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarPrice extends Model
{
    protected $fillable = [
        'duration_type',
        'price',
        'discounted_price',
        'discount_start_at',
        'discount_end_at',
        'is_active',
        'car_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'duration_type' => CarPriceDurationTypeEnum::class,
            'discounted_price' => 'decimal:2',
            'discount_start_at' => 'datetime',
            'discount_end_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Determine if the discount is active for the given time (now by default).
     */
    public function isDiscountActive(?Carbon $now = null): bool
    {
        $now = $now ?: now();

        if ($this->discounted_price === null) {
            return false;
        }

        $startsOk = $this->discount_start_at === null || $this->discount_start_at->lte($now);
        $endsOk = $this->discount_end_at === null || $this->discount_end_at->gte($now);

        return $startsOk && $endsOk;
    }

    /**
     * Get the current effective price, using discounted price when active.
     */
    public function effectivePrice(?Carbon $now = null): string
    {
        return $this->isDiscountActive($now)
            ? (string) $this->discounted_price
            : (string) $this->price;
    }
}
