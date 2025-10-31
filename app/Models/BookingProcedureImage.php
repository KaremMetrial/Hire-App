<?php

namespace App\Models;

use App\Enums\CarImageTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingProcedureImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_procedure_id',
        'image_path',
        'image_type',
        'uploaded_by',
    ];

    protected $casts = [
        'image_type' => CarImageTypeEnum::class,
    ];

    // Relationships
    public function procedure(): BelongsTo
    {
        return $this->belongsTo(BookingProcedure::class, 'booking_procedure_id');
    }

    // Scopes
    public function scopeByUser($query)
    {
        return $query->where('uploaded_by', 'user');
    }

    public function scopeByVendor($query)
    {
        return $query->where('uploaded_by', 'vendor');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('image_type', $type);
    }

    // Helper Methods
    public function isByUser(): bool
    {
        return $this->uploaded_by === 'user';
    }

    public function isByVendor(): bool
    {
        return $this->uploaded_by === 'vendor';
    }

    public function getImageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
