<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::set('cancellation_policy', [
            'en' => 'Cancellation Policy: Free cancellation and full refund before 9 hours of booking date.',
            'ar' => 'سياسة الإلغاء: الإلغاء مجاني واسترداد كامل المبلغ قبل 9 ساعات من تاريخ الحجز.',
        ]);
    }
}
