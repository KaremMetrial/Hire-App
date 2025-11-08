<?php

    namespace App\Http\Resources\Vendor;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class VendorResourece extends JsonResource
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
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status?->value,
                'status' => $this->status?->label(),
                'is_active' => (bool)$this->is_active,
                'created_at' => $this->created_at,
                'role' => $this->rentalShops()->first()?->pivot->role,
            ];
        }
    }
