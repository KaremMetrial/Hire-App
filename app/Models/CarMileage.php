<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarMileage extends Model
{
    protected $fillable = [
        'limit_km_per_day',
        'limit_km_per_hour',
        'extra_fee',
        'car_id',
    ];
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
