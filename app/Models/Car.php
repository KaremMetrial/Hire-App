<?php

namespace App\Models;

use App\Enums\DeliveryOptionTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    protected $fillable = [
        'year_of_manufacture',
        'color',
        'license_plate',
        'num_of_seat',
        'kilometers',
        'model_id',
        'fuel_id',
        'transmission_id',
        'category_id',
        'rental_shop_id',
        'city_id',
        'is_active',
        'rental_shop_rule',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'model_id');
    }

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }

    public function transmission(): BelongsTo
    {
        return $this->belongsTo(Transmission::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rentalShop(): BelongsTo
    {
        return $this->belongsTo(RentalShop::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(CarPrice::class);
    }

    public function mileages(): HasOne
    {
        return $this->hasOne(CarMileage::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(CarAvailability::class);
    }

    public function insurances(): BelongsToMany
    {
        return $this->belongsToMany(Insurance::class);
    }

    public function deliveryOptions(): HasMany
    {
        return $this->hasMany(DeliveryOption::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CarRule::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(ExtraService::class)
            ->withPivot('price');
    }

    /**
     * check if car is available at specific date
     *
     * @param  string  $date  format: Y-m-d
     */
    public function isAvailable(string $date): bool
    {
        $isUnavailable = $this->availabilities()
            ->whereNotNull('unavailable_from')
            ->whereNotNull('unavailable_to')
            ->where('unavailable_from', '<=', $date)
            ->where('unavailable_to', '>=', $date)
            ->exists();

        return ! $isUnavailable;
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
        return $this->hasMany(BookingReview::class)->where('is_approved', true);
    }

    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    public function isAvailableForPeriod($pickupDate, $returnDate): bool
    {
        return ! $this->bookings()
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($query) use ($pickupDate, $returnDate) {
                $query->whereBetween('pickup_date', [$pickupDate, $returnDate])
                    ->orWhereBetween('return_date', [$pickupDate, $returnDate])
                    ->orWhere(function ($q) use ($pickupDate, $returnDate) {
                        $q->where('pickup_date', '<=', $pickupDate)
                            ->where('return_date', '>=', $returnDate);
                    });
            })
            ->exists();
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks');
    }

    /**
     * Check if car is bookmarked by a specific user
     */
    public function isBookmarkedBy(int $userId): bool
    {
        // Check if bookmarks relationship is already loaded
        if ($this->relationLoaded('bookmarks')) {
            return $this->bookmarks->contains('user_id', $userId);
        }

        return $this->bookmarks()->where('user_id', $userId)->exists();
    }

    /**
     * Get bookmark count for this car
     */
    public function getBookmarkCountAttribute(): int
    {
        return $this->bookmarks()->count();
    }

    /**
     * Check if car can be delivered to user location
     */
    public function canBeDelivered(): bool
    {
        return $this->deliveryOptions()
            ->where('is_active', true)
            ->where('type', DeliveryOptionTypeEnum::CUSTOM)
            ->exists();
    }
}
