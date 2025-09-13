<?php

    namespace App\Models;

    use App\Enums\CarImageTypeEnum;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class CarImage extends Model
    {
        protected $fillable = [
            'image',
            'image_name',
            'car_id',
        ];
        protected $casts = [
            'image_name' => CarImageTypeEnum::class,
        ];

        public function car(): BelongsTo
        {
            return $this->belongsTo(Car::class);
        }
    }
