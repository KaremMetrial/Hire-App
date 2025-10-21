<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'rental_shop_id',
        'car_id',
        'rating',
        'cleanliness_rating',
        'service_rating',
        'value_rating',
        'comment',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'service_rating' => 'integer',
        'value_rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rentalShop(): BelongsTo
    {
        return $this->belongsTo(RentalShop::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function getAverageRating(): float
    {
        $ratings = array_filter([
            $this->rating,
            $this->cleanliness_rating,
            $this->service_rating,
            $this->value_rating,
        ]);

        return count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
