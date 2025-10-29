<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingInformationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'requested_field',
        'is_required',
        'status',
        'notes',
        'submitted_value',
        'submitted_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function markAsSubmitted(string $value = null): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_value' => $value,
            'submitted_at' => now(),
        ]);
    }

    public function markAsApproved(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function markAsRejected(): void
    {
        $this->update(['status' => 'rejected']);
    }

    // Get field label for display (localized)
    public function getFieldLabel(): string
    {
        return __('information_request_fields.' . $this->requested_field, [], app()->getLocale());
    }
}
