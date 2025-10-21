<?php

namespace App\Models;

use App\Enums\DeliveryOptionTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOption extends Model
{
    protected $fillable = [
        'type',
        'is_active',
        'is_default',
        'price',
        'car_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'price' => 'decimal:2',
        'type' => DeliveryOptionTypeEnum::class,
    ];

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /*
     * Relation
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
