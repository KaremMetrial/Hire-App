<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'insurance_id',
        'price',
        'deposit_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'deposit_price' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class);
    }
}
