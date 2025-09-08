<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        ];
        protected $cast = [
            'is_active' => 'boolean'
        ];

        #[Scope]
        protected function active(Builder $query): Builder
        {
            return $query->where('is_active', true);
        }

        public function carModel(): BelongsTo
        {
            return $this->belongsTo(CarModel::class);
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
    }
