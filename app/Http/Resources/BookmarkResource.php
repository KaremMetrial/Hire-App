<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookmarkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'car_id' => $this->car_id,

            // Car Information
            'car' => $this->whenLoaded('car', function () {
                return new CarResource($this->car);
            }),

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // Relative time for better UX
            'booked_at' => $this->created_at->diffForHumans(),
            'booked_at_formatted' => $this->created_at->format('M d, Y h:i A'),
        ];
    }
}
