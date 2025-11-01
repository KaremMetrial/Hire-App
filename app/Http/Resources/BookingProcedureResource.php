<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingProcedureResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'type' => $this->type,
            'type_label' => $this->isReturn() ? __('enums.procedure_types.return') : __('enums.procedure_types.pickup'),
            'submitted_by' => $this->submitted_by,
            'notes' => $this->notes,
            'confirmed_by_vendor' => $this->confirmed_by_vendor,
            'confirmed_at' => $this->confirmed_at,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'images' => BookingProcedureImageResource::collection($this->whenLoaded('images')),
            'is_return_procedure' => $this->isReturn(),
            'is_pickup_procedure' => $this->isPickup(),
        ];
    }
}
