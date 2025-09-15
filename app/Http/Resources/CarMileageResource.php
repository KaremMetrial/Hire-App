<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarMileageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'limit_km_per_day' => $this->limit_km_per_day,
            'limit_km_per_hour' => $this->limit_km_per_hour,
            'extra_fee' => $this->extra_fee,
        ];
    }
}
