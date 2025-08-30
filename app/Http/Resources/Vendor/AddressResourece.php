<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResourece extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'country' => $this->country_id ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'code' => $this->country->code
            ] : null,
            'city_id' => $this->city_id ? [
                'id' => $this->city_id,
                'name' => $this->city->name,
            ] : null,
        ];
    }
}
