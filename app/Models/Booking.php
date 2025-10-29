<?php

namespace App\Models;

use App\Enums\BookingStatusEnum;
use App\Enums\DeliveryOptionTypeEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'user_id',
        'car_id',
        'rental_shop_id',
        'pickup_date',
        'return_date',
        'pickup_location_type',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'return_location_type',
        'return_address',
        'return_latitude',
        'return_longitude',
        'rental_price',
        'delivery_fee',
        'extra_services_total',
        'insurance_total',
        'mileage_fee',
        'tax',
        'discount',
        'total_price',
        'deposit_amount',
        'status',
        'payment_status',
        'pickup_mileage',
        'return_mileage',
        'actual_mileage_used',
        'customer_notes',
        'admin_notes',
        'cancellation_reason',
        'rejection_reason',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'pickup_date' => 'datetime',
        'return_date' => 'datetime',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'return_latitude' => 'decimal:8',
        'return_longitude' => 'decimal:8',
        'rental_price' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'extra_services_total' => 'decimal:2',
        'insurance_total' => 'decimal:2',
        'mileage_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => BookingStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
        'pickup_location_type' => DeliveryOptionTypeEnum::class,
        'return_location_type' => DeliveryOptionTypeEnum::class,
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function rentalShop(): BelongsTo
    {
        return $this->belongsTo(RentalShop::class);
    }

    public function extraServices(): HasMany
    {
        return $this->hasMany(BookingExtraService::class);
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(BookingInsurance::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(BookingReview::class);
    }

    public function informationRequests(): HasMany
    {
        return $this->hasMany(BookingInformationRequest::class);
    }

    public function pickupIssues(): HasMany
    {
        return $this->hasMany(BookingPickupIssue::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeInfoRequested($query)
    {
        return $query->where('status', 'info_requested');
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === BookingStatusEnum::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatusEnum::Confirmed;
    }

    public function isActive(): bool
    {
        return $this->status === BookingStatusEnum::Active;
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatusEnum::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatusEnum::Cancelled;
    }

    public function isRejected(): bool
    {
        return $this->status === BookingStatusEnum::Rejected;
    }

    public function isInfoRequested(): bool
    {
        return $this->status === BookingStatusEnum::InfoRequested;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [BookingStatusEnum::Pending, BookingStatusEnum::Confirmed]);
    }

    public function getDurationInDays(): int
    {
        return $this->pickup_date->diffInDays($this->return_date);
    }

    public function getDurationInHours(): int
    {
        return $this->pickup_date->diffInHours($this->return_date);
    }

    public function calculateTotalPrice(): float
    {
        return $this->rental_price
            + $this->delivery_fee
            + $this->extra_services_total
            + $this->insurance_total
            + $this->mileage_fee
            + $this->tax
            - $this->discount;
    }

    // Boot method for generating booking number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
