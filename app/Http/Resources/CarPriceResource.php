<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price, // Assuming this attribute exists
            'duration_type' => $this->duration_type, // Assuming this attribute exists
        ];
    }
}
