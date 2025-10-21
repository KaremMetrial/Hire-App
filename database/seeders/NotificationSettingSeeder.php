<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class NotificationSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Create notification settings for all users
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                NotificationSetting::create([
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'push_notification_enabled' => true,
                    'sound_enabled' => true,
                    'all_notification_disabled' => false,
                ]);
            }
        });

        // Create notification settings for all vendors
        Vendor::chunk(100, function ($vendors) {
            foreach ($vendors as $vendor) {
                NotificationSetting::create([
                    'notifiable_type' => Vendor::class,
                    'notifiable_id' => $vendor->id,
                    'push_notification_enabled' => true,
                    'sound_enabled' => true,
                    'all_notification_disabled' => false,
                ]);
            }
        });

        // Create some specific notification settings with different preferences
        $specificSettings = [
            [
                'user_email' => 'john.smith@example.com',
                'push_notification_enabled' => true,
                'sound_enabled' => false,
                'all_notification_disabled' => false,
            ],
            [
                'user_email' => 'sarah.johnson@example.com',
                'push_notification_enabled' => false,
                'sound_enabled' => false,
                'all_notification_disabled' => true,
            ],
            [
                'vendor_email' => 'premium@hireapp.com',
                'push_notification_enabled' => true,
                'sound_enabled' => true,
                'all_notification_disabled' => false,
            ],
            [
                'vendor_email' => 'cityauto@hireapp.com',
                'push_notification_enabled' => true,
                'sound_enabled' => true,
                'all_notification_disabled' => false,
            ],
        ];

        foreach ($specificSettings as $setting) {
            if (isset($setting['user_email'])) {
                $user = User::where('email', $setting['user_email'])->first();
                if ($user) {
                    NotificationSetting::updateOrCreate(
                        [
                            'notifiable_type' => User::class,
                            'notifiable_id' => $user->id,
                        ],
                        [
                            'push_notification_enabled' => $setting['push_notification_enabled'],
                            'sound_enabled' => $setting['sound_enabled'],
                            'all_notification_disabled' => $setting['all_notification_disabled'],
                        ]
                    );
                }
            } elseif (isset($setting['vendor_email'])) {
                $vendor = Vendor::where('email', $setting['vendor_email'])->first();
                if ($vendor) {
                    NotificationSetting::updateOrCreate(
                        [
                            'notifiable_type' => Vendor::class,
                            'notifiable_id' => $vendor->id,
                        ],
                        [
                            'push_notification_enabled' => $setting['push_notification_enabled'],
                            'sound_enabled' => $setting['sound_enabled'],
                            'all_notification_disabled' => $setting['all_notification_disabled'],
                        ]
                    );
                }
            }
        }
    }
}
