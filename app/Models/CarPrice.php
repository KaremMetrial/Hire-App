<?php

namespace App\Models;

use App\Enums\CarPriceDurationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarPrice extends Model
{
    protected $fillable = [
        'duration_type',
        'price',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'duration_type' => CarPriceDurationTypeEnum ::class,
    ];
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
