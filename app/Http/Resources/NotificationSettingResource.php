<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'push_notification_enabled' => $this->push_notification_enabled,
            'sound_enabled' => $this->sound_enabled,
            'all_notification_disabled' => $this->all_notification_disabled,
        ];
    }
}
