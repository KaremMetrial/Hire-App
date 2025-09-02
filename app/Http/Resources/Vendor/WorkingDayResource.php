<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingDayResource extends JsonResource
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
            'day_of_week' => $this->day_of_week->value,
            'day_of_week_label' => $this->day_of_week->label(),
            'open_time' => $this->open_time->format('H:i'),
            'close_time' => $this->close_time->format('H:i'),
        ];
    }
}
