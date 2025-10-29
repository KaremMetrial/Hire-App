<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarRule extends Model
{
    protected $fillable = [
        'car_id',
        'text',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
