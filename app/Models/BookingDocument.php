<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'document_id',
        'file_path',
        'document_value',
        'verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified === true;
    }
}
