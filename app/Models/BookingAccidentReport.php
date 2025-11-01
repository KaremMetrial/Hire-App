<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingAccidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'accident_location',
        'accident_details',
        'accident_location_coordinates',
        'accident_date',
        'severity',
        'status',
        'admin_notes',
        'resolved_at',
    ];

    protected $casts = [
        'accident_location_coordinates' => 'array',
        'accident_date' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BookingAccidentReportImage::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('status', 'investigating');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInvestigating(): bool
    {
        return $this->status === 'investigating';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function markAsInvestigating(): void
    {
        $this->update(['status' => 'investigating']);
    }

    public function markAsResolved(?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'admin_notes' => $adminNotes,
            'resolved_at' => now(),
        ]);
    }
}
