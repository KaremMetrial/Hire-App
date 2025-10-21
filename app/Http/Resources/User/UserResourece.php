<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourece extends JsonResource
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
            'face_license_id_photo' => $this->face_license_id_photo ? asset(
                'storage/'.$this->face_license_id_photo
            ) : null,
            'back_license_id_photo' => $this->back_license_id_photo ? asset(
                'storage/'.$this->back_license_id_photo
            ) : null,
            'birthday' => $this->birthday,
        ];
    }
}
