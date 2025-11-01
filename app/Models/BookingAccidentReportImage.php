<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookingAccidentReportImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_accident_report_id',
        'image_path',
        'description',
    ];

    // Relations
    public function accidentReport(): BelongsTo
    {
        return $this->belongsTo(BookingAccidentReport::class);
    }

    // Helper Methods
    public function getImageUrl(): string
    {
        return Storage::url($this->image_path);
    }

    public function getFullImagePath(): string
    {
        return Storage::path($this->image_path);
    }
}
