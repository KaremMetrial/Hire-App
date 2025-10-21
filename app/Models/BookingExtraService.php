<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtraService extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'extra_service_id',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function extraService(): BelongsTo
    {
        return $this->belongsTo(ExtraService::class);
    }

    public function getTotal(): float
    {
        return $this->price * $this->quantity;
    }
}
