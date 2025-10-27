<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the car that is bookmarked.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Scope a query to only include bookmarks for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include bookmarks for a specific car.
     */
    public function scopeForCar($query, $carId)
    {
        return $query->where('car_id', $carId);
    }

    /**
     * Check if a user has bookmarked a specific car.
     */
    public static function isBookmarked(int $userId, int $carId): bool
    {
        return static::where('user_id', $userId)
            ->where('car_id', $carId)
            ->exists();
    }

    /**
     * Toggle bookmark for a user and car.
     * Returns true if bookmarked, false if removed.
     */
    public static function toggle(int $userId, int $carId): bool
    {
        $bookmark = static::where('user_id', $userId)
            ->where('car_id', $carId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return false;
        } else {
            static::create([
                'user_id' => $userId,
                'car_id' => $carId,
            ]);
            return true;
        }
    }
}
