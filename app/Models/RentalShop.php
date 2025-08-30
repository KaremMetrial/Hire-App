<?php

    namespace App\Models;

    use App\Enums\RentalShopStatusEnum;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\MorphOne;

    class RentalShop extends Model
    {
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
        ];
        protected $casts = [
            'is_active' => 'boolean',
            'status' => RentalShopStatusEnum::class,
            'actioned_at' => 'datetime',
        ];

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
    }
