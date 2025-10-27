<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingReviewResource extends JsonResource
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
            'rating' => (int) $this->rating,
            'cleanliness_rating' => (int) $this->cleanliness_rating,
            'service_rating' => (int) $this->service_rating,
            'value_rating' => (int) $this->value_rating,
            'average_rating' => round($this->getAverageRating(), 1),
            'comment' => $this->comment,
            'is_approved' => (bool) $this->is_approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'car' => $this->whenLoaded('car', function () {
                return [
                    'id' => $this->car->id,
                    'year_of_manufacture' => $this->car->year_of_manufacture,
                    'color' => $this->car->color,
                    'license_plate' => $this->car->license_plate,
                    'car_model' => [
                        'id' => $this->car->carModel->id,
                        'name' => $this->car->carModel->name,
                    ],
                ];
            }),
            'booking' => $this->whenLoaded('booking', function () {
                return [
                    'id' => $this->booking->id,
                    'booking_number' => $this->booking->booking_number,
                    'pickup_date' => $this->booking->pickup_date,
                    'return_date' => $this->booking->return_date,
                ];
            }),
        ];
    }
}
