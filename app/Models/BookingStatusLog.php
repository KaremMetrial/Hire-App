<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'old_status',
        'new_status',
        'changed_by_type',
        'changed_by_id',
        'notes',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy(): MorphTo
    {
        return $this->morphTo('changed_by', 'changed_by_type', 'changed_by_id');
    }

    public function scopeWithChangedBy($query)
    {
        return $query->with(['changedBy' => function ($q) {
            $q->select('id', 'name');
        }]);
    }
}
