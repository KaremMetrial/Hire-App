<?php

namespace App\Http\Resources;

use App\Http\Resources\Vendor\RentalShopResourece;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CarCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $title = $this->whenLoaded('carModel', function () {
            $brandName = $this->carModel->brand->name ?? '';
            $modelName = $this->carModel->name ?? '';
            return trim("{$brandName} {$modelName} {$this->year_of_manufacture}");
        }, '');

        return [
            'id' => $this->id,
            'title' => $title,
            'kilometers' => (int)$this->kilometers,
            'is_active' => (bool)$this->is_active,

//            'rental_shop' => new RentalShopResourece($this->whenLoaded('rentalShop')),
            'rental_shop' => $this->whenLoaded('rentalShop', function ($rentalShop) {
                return [
                    'id' => $rentalShop->id,
                    'name' => $rentalShop->name,
                    'image' => $rentalShop->image ? asset('storage/' . $rentalShop->image) : null,
                    'is_active' => (bool)$rentalShop->is_active,
                    'rating' => (int)$rentalShop->rating,
                    'count_rating' => (int)$rentalShop->count_rating,
                ];
            }),
            'prices' => CarPriceResource::collection($this->whenLoaded('prices')),
            'is_available' => (bool)$this->isAvailable(Carbon::now()),
            'can_be_delivered' => (bool)$this->canBeDelivered(),

            'images' => CarImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
