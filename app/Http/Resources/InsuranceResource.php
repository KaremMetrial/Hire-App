<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'period' => $this->period->value,
            'period_label' => $this->period->label(),
            'price' => $this->price,
            'deposit_price' => $this->deposit_price,
            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
        ];
    }
}
