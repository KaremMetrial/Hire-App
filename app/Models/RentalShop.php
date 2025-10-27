<?php

namespace App\Models;

use App\Enums\RentalShopStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalShop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'image',
        'is_active',
        'status',
        'actioned_at',
        'rejected_reason',
        'transport_license_photo',
        'commerical_registration_photo',
        'rating',
        'count_rating',
        'actioned_by',
        'facebook_link',
        'instagram_link',
        'whatsapp_link',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'status' => RentalShopStatusEnum::class,
        'actioned_at' => 'datetime',
    ];

    #[Scope]
    public function searchName(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /*
     * Relationships to Vendor
     */
    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'rental_shop_vendor')
            ->withPivot('role')
            ->withTimestamps();
    }

    /*
     * Relationships to Address
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /*
     * Relationships to WorkingDay
     */
    public function workingDays(): HasMany
    {
        return $this->hasMany(WorkingDay::class);
    }

    public function documents()
    {
        return $this->belongsToMany(
            Document::class,
            'customer_type_document_rental_shop',
            'rental_shop_id',
            'document_id'
        )->withPivot('customer_type_id')->withTimestamps();
    }

    public function customer_types()
    {
        return $this->belongsToMany(
            CustomerType::class,
            'customer_type_document_rental_shop',
            'rental_shop_id',
            'customer_type_id'
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BookingReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(BookingReview::class)->approved();
    }

    public function allReviews(): HasMany
    {
        return $this->hasMany(BookingReview::class);
    }

    public function getTotalReviewsAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    public function getStarDistributionAttribute(): array
    {
        $distribution = $this->approvedReviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Ensure all ratings (1-5) are present with 0 count if missing
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[$i] = $distribution[$i] ?? 0;
        }

        return $stars;
    }

    public function getReviewStatsAttribute(): array
    {
        return [
            'total_reviews' => $this->total_reviews,
            'average_rating' => round($this->average_rating, 1),
            'star_distribution' => [
                '5_star' => $this->star_distribution[5] ?? 0,
                '4_star' => $this->star_distribution[4] ?? 0,
                '3_star' => $this->star_distribution[3] ?? 0,
                '2_star' => $this->star_distribution[2] ?? 0,
                '1_star' => $this->star_distribution[1] ?? 0,
            ],
        ];
    }
}
