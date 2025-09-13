<?php

namespace App\Http\Resources;

use App\Filament\Resources\RentalShops\RentalShopResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'year_of_manufacture' => $this->year_of_manufacture,
            'color' => $this->color,
            'license_plate' => $this->license_plate,
            'num_of_seat' => $this->num_of_seat,
            'kilometers' => $this->kilometers,
            'is_active' => $this->is_active,
            'model' => new ModelResource($this->whenLoaded('carModel')),
            'fuel' => new FuelResource($this->whenLoaded('fuel')),
            'transmission' => new TransmissionResource($this->whenLoaded('transmission')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'rental_shop' => new RentalShopResource($this->whenLoaded('rentalShop')),
            'city' => new CityResource($this->whenLoaded('city')),
            'images' => CarImageResource::collection($this->whenLoaded('images')),
            'prices' => CarPriceResource::collection($this->whenLoaded('prices')),
            'mileages' => new CarMileageResource($this->whenLoaded('mileages')),
            'availabilities' => CarAvailabilityResource::collection($this->whenLoaded('availabilities')),
            'insurances' => InsuranceResource::collection($this->whenLoaded('insurances')),
        ];
    }
}
