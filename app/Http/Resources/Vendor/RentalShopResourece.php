<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalShopResourece extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'is_active' => (bool) $this->is_active,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'actioned_at' => $this->actioned_at,
            'rejected_reason' => $this->rejected_reason,
            'rating' => (int) $this->rating,
            'count_rating' => (int) $this->count_rating,
            'facebook_link' => $this->facebook_link,
            'instagram_link' => $this->instagram_link,
            'whatsapp_link' => $this->whatsapp_link,
            'address' => new AddressResourece($this->whenLoaded('address')),
            'working_days' => WorkingDayResource::collection($this->workingDays),
        ];
    }
}
