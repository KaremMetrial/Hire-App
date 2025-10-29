<?php

namespace App\Http\Resources;

use App\Http\Resources\Vendor\RentalShopResourece;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user('user');

        if ($user) {
            $this->load('bookmarks');
        }

        return [
            'id' => $this->id,
            'year_of_manufacture' => $this->year_of_manufacture,
            'color' => $this->color,
            'license_plate' => $this->license_plate,
            'num_of_seat' => (int) $this->num_of_seat,
            'kilometers' => (int) $this->kilometers,
            'is_active' => (bool) $this->is_active,
            'model' => new ModelResource($this->whenLoaded('carModel')),
            'fuel' => new FuelResource($this->whenLoaded('fuel')),
            'transmission' => new TransmissionResource($this->whenLoaded('transmission')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'rental_shop' => new RentalShopResourece($this->whenLoaded('rentalShop')),
            'city' => new CityResource($this->whenLoaded('city')),
            'images' => CarImageResource::collection($this->whenLoaded('images')),
            'prices' => CarPriceResource::collection($this->whenLoaded('prices')),
            'mileages' => new CarMileageResource($this->whenLoaded('mileages')),
            'availabilities' => CarAvailabilityResource::collection($this->whenLoaded('availabilities')),
            'insurances' => InsuranceResource::collection($this->whenLoaded('insurances')),
            'is_available' => (bool) $this->isAvailable(Carbon::now()),
            'can_be_delivered' => (bool) $this->canBeDelivered(),
            'delivery_options' => DeliveryOptionResource::collection($this->whenLoaded('deliveryOptions')),
            'services' => ExtraServiceResource::collection($this->whenLoaded('services')),
                        'rules' => CarRuleResource::collection($this->whenLoaded('rules')),

            // Bookmark information
            'is_bookmarked' => $this->when($user, function () use ($user) {
                if (!$user) {
                    return false;
                }
                // Debug: Check if user ID exists
                if (!$user->id) {
                    return false;
                }
                return $this->isBookmarkedBy($user->id);
            }),

            'customer_types' => $this->whenLoaded('rentalShop', function () {
                if (! $this->rentalShop) {
                    return [];
                }

                $this->rentalShop->loadMissing(['documents', 'customer_types']);

                $documentsByCustomerType = $this->rentalShop->documents->groupBy('pivot.customer_type_id');

                $customerTypes = $this->rentalShop->customer_types->unique('id');

                $customerTypes->each(function ($customerType) use ($documentsByCustomerType) {
                    $customerType->setRelation('documents', $documentsByCustomerType->get($customerType->id, collect()));
                });

                return CustomerTypeResource::collection($customerTypes);
            }),
        ];
    }
}
