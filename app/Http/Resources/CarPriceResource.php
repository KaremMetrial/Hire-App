<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isDiscounted = $this->isDiscountActive();

        return [
            'id' => $this->id,
            'duration_type' => $this->duration_type,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'discount_start_at' => optional($this->discount_start_at)->toISOString(),
            'discount_end_at' => optional($this->discount_end_at)->toISOString(),
            'is_discounted' => $isDiscounted,
            'effective_price' => $this->effectivePrice(),
        ];
    }
}
