<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Vendor\AddressResourece;
use App\Http\Resources\Vendor\WorkingDayResource;
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
//            'actioned_at' => $this->actioned_at,
//            'rejected_reason' => $this->rejected_reason,
            'rating' => (int) $this->rating,
            'count_rating' => (int) $this->count_rating,
            'facebook_link' => $this->facebook_link,
            'instagram_link' => $this->instagram_link,
            'whatsapp_link' => $this->whatsapp_link,
            'review_stats' => $this->whenLoaded('approvedReviews', function () {
                return [
                    'total_reviews' => $this->total_reviews,
                    'average_rating' => round($this->average_rating, 1),
                    'all_reviews_count' => $this->allReviews->count(),
                    'approved_reviews_count' => $this->approvedReviews->count(),
                    'star_distribution' => [
                        '5_star' => $this->star_distribution[5] ?? 0,
                        '4_star' => $this->star_distribution[4] ?? 0,
                        '3_star' => $this->star_distribution[3] ?? 0,
                        '2_star' => $this->star_distribution[2] ?? 0,
                        '1_star' => $this->star_distribution[1] ?? 0,
                    ],
                ];
            }),
            'address' => new AddressResourece($this->whenLoaded('address')),
            'working_days' => WorkingDayResource::collection($this->whenLoaded('workingDays')),
        ];
    }
}
