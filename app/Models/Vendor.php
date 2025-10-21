<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use App\Observers\VendorObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([VendorObserver::class])]
class Vendor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'national_id_photo',
        'status',
        'actioned_at',
        'rejected_reason',
        'actioned_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'status' => VendorStatusEnum::class,
        'actioned_at' => 'datetime',
    ];

    /*
     * Relationships to Admin
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /*
     * OTP Relationship
     */
    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

    /*
     * Rental Shop Relationship
     */
    public function rentalShops(): BelongsToMany
    {
        return $this->belongsToMany(RentalShop::class, 'rental_shop_vendor')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function notificationSetting(): MorphOne
    {
        return $this->morphOne(NotificationSetting::class, 'notifiable');
    }
}
