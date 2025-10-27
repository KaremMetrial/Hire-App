<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarAvailability extends Model
{
    protected $fillable = [
        'is_available',
        'unavailable_from',
        'unavailable_to',
        'reason',
        'car_id',
    ];
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
