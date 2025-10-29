<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPickupIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'problem_details',
        'image_path',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($issue) {
            if (empty($issue->reported_at)) {
                $issue->reported_at = now();
            }
        });
    }
}
