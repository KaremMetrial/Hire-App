<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingInformationRequestResource extends JsonResource
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
            'requested_field' => $this->requested_field,
            'field_label' => $this->getFieldLabel(),
            'is_required' => $this->is_required,
            'status' => $this->status,
            'notes' => $this->notes,
            'submitted_value' => $this->submitted_value,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
