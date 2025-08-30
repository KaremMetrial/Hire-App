<?php

namespace App\Http\Resources\Vendor;

use App\Enums\RentalShopStatusEnum;
use App\Models\Vendor;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'image' => $this->image ? asset('storage/'. $this->image) : null,
            'is_active' => (bool) $this->is_active,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'actioned_at' => $this->actioned_at,
            'rejected_reason' => $this->rejected_reason,
            'rating' => $this->rating,
            'count_rating' => $this->count_rating,
            'address' => new AddressResourece($this->address),
        ];
    }
}
