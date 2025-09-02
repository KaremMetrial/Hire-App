<?php

    namespace App\Models;

    use App\Enums\DayOfWeekEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class WorkingDay extends Model
    {
        use HasFactory;

        protected $table = 'working_days';

        protected $fillable = [
            'day_of_week',
            'open_time',
            'close_time',
            'rental_shop_id',
        ];

        protected $casts = [
            'day_of_week' => DayOfWeekEnum::class,
            'open_time' => 'datetime:H:i',
            'close_time' => 'datetime:H:i',
        ];

        /**
         * Each working day belongs to a rental shop.
         */
        public function rentalShop(): BelongsTo
        {
            return $this->belongsTo(RentalShop::class);
        }

    }
