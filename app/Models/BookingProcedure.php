<?php

namespace App\Models;

use App\Enums\CarImageTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'type',
        'submitted_by',
        'notes',
        'confirmed_by_vendor',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_by_vendor' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    // Relationships
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
        return $this->hasMany(BookingProcedureImage::class);
    }

    // Scopes
    public function scopePickup($query)
    {
        return $query->where('type', 'pickup');
    }

    public function scopeReturn($query)
    {
        return $query->where('type', 'return');
    }

    public function scopeByUser($query)
    {
        return $query->where('submitted_by', 'user');
    }

    public function scopeByVendor($query)
    {
        return $query->where('submitted_by', 'vendor');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('confirmed_by_vendor', true);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->where('confirmed_by_vendor', false);
    }

    // Helper Methods
    public function isPickup(): bool
    {
        return $this->type === 'pickup';
    }

    public function isReturn(): bool
    {
        return $this->type === 'return';
    }

    public function isByUser(): bool
    {
        return $this->submitted_by === 'user';
    }

    public function isByVendor(): bool
    {
        return $this->submitted_by === 'vendor';
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_by_vendor;
    }

    public function markAsConfirmed(): void
    {
        $this->update([
            'confirmed_by_vendor' => true,
            'confirmed_at' => now(),
        ]);
    }

    // Get images by type
    public function getImagesByType(string $type): HasMany
    {
        return $this->images()->where('image_type', $type);
    }
}
