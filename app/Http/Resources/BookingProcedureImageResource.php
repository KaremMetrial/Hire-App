<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingProcedureImageResource extends JsonResource
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
            'booking_procedure_id' => $this->booking_procedure_id,
            'image_path' => $this->image_path,
            'image_url' => $this->getImageUrl(),
            'image_type' => $this->image_type->value,
            'type_label' => $this->image_type->label(),
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
