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
            'start_date' => $this->start_date, // Assuming this attribute exists
            'end_date' => $this->end_date, // Assuming this attribute exists
            'is_available' => $this->is_available, // Assuming this attribute exists
        ];
    }
}
