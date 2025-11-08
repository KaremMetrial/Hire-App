<?php

namespace App\Http\Resources;

use App\Http\Resources\Vendor\AddressResourece;
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

        // Generate car title combining brand, model, and year for better display
        $title = $this->whenLoaded('carModel', function () {
            $brandName = $this->carModel->brand->name ?? '';
            $modelName = $this->carModel->name ?? '';
            return trim("{$brandName} {$modelName} {$this->year_of_manufacture}");
        }, '');

        return [
            // Basic car information
            'id' => $this->id,
            'title' => $title,
            'subtitle' => [
                'transmission' => $this->transmission->name ?? null,
                'fuel' => $this->fuel->name ?? null,
                'category' => $this->category->name ?? null,
                'num_of_seat' => (int) $this->num_of_seat,
            ],
            'kilometers' => (int) $this->kilometers,
            'prices' => CarPriceResource::collection($this->whenLoaded('prices')),
            'rental_shop' => $this->whenLoaded('rentalShop', function ($rentalShop) {
                return [
                    'id' => $rentalShop->id,
                    'name' => $rentalShop->name,
                    'image' => $rentalShop->image ? asset('storage/'.$rentalShop->image) : null,
                    'is_active' => (bool) $rentalShop->is_active,
                    'rating' => (int) $rentalShop->rating,
                    'count_rating' => (int) $rentalShop->count_rating,
                    'created_at' => $rentalShop->created_at->diffForHumans(),
                    'address' => new AddressResourece($rentalShop->address),
                ];
            }),
            'rules' => CarRuleResource::collection($this->whenLoaded('rules')),
            'cancellation_policy' => config('booking.cancellation_policy'),
            'is_active' => (bool) $this->is_active,
            'is_available' => (bool) $this->isAvailable(Carbon::now()),
            'can_be_delivered' => (bool) $this->canBeDelivered(),
            'images' => CarImageResource::collection($this->whenLoaded('images')),
            'mileages' => new CarMileageResource($this->whenLoaded('mileages')),
            'insurances' => InsuranceResource::collection($this->whenLoaded('insurances')),
            'delivery_options' => DeliveryOptionResource::collection($this->whenLoaded('deliveryOptions')),
            'services' => ExtraServiceResource::collection($this->whenLoaded('services')),
            'is_bookmarked' => $this->when($user, function () use ($user) {
                if (!$user) {
                    return false;
                }
                if (!$user->id) {
                    return false;
                }
                return $this->isBookmarkedBy($user->id);
            }),
            'customer_types' => $this->whenLoaded('rentalShop', function () {
                if (!$this->rentalShop) {
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
