<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_available' => (bool) $this->is_available,
            'unavailable_from' => $this->unavailable_from?->format('Y-m-d'),
            'unavailable_to' => $this->unavailable_to?->format('Y-m-d'),
            'reason' => $this->reason,
        ];
    }
}
