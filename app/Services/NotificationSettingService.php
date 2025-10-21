<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\Vendor;

class NotificationSettingService
{
    public function getSettings(Vendor $vendor): NotificationSetting
    {
        return $vendor->notificationSetting ?? $vendor->notificationSetting()->create([
            'push_notification_enabled' => true,
            'sound_enabled' => true,
            'all_notification_disabled' => false,
        ]);
    }

    public function updateSettings(Vendor $vendor, array $data): NotificationSetting
    {
        $settings = $this->getSettings($vendor);
        $settings->update($data);

        return $settings;
    }
}
