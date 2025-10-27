<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'face_license_id_photo',
        'back_license_id_photo',
        'birthday',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
     * OTP Relationship
     */
    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BookingReview::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedCars(): HasManyThrough
    {
        return $this->hasManyThrough(Car::class, Bookmark::class, 'user_id', 'id', 'id', 'car_id');
    }

    /**
     * Check if user has bookmarked a specific car
     */
    public function hasBookmarked(int $carId): bool
    {
        return $this->bookmarks()->where('car_id', $carId)->exists();
    }

    /**
     * Bookmark a car
     */
    public function bookmarkCar(int $carId): Bookmark
    {
        return $this->bookmarks()->firstOrCreate(['car_id' => $carId]);
    }

    /**
     * Remove bookmark from a car
     */
    public function unbookmarkCar(int $carId): bool
    {
        return $this->bookmarks()->where('car_id', $carId)->delete() > 0;
    }

    /**
     * Toggle bookmark for a car
     */
    public function toggleBookmark(int $carId): bool
    {
        return Bookmark::toggle($this->id, $carId);
    }
}
