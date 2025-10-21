<?php

namespace Database\Seeders;

use App\Models\Transmission;
use Illuminate\Database\Seeder;

class TransmissionSeeder extends Seeder
{
    public function run(): void
    {
        $transmissions = [
            [
                'en' => ['name' => 'Automatic'],
                'ar' => ['name' => 'أوتوماتيك'],
            ],
            [
                'en' => ['name' => 'Manual'],
                'ar' => ['name' => 'يدوي'],
            ],
            [
                'en' => ['name' => 'CVT'],
                'ar' => ['name' => 'ناقل حركة متغير باستمرار'],
            ],
            [
                'en' => ['name' => 'Semi-Automatic'],
                'ar' => ['name' => 'شبه أوتوماتيك'],
            ],
            [
                'en' => ['name' => 'Dual-Clutch'],
                'ar' => ['name' => 'قابض مزدوج'],
            ],
        ];

        foreach ($transmissions as $transmission) {
            $transmissionModel = Transmission::create();

            $transmissionModel->translateOrNew('en')->name = $transmission['en']['name'];
            $transmissionModel->translateOrNew('ar')->name = $transmission['ar']['name'];
            $transmissionModel->save();
        }
    }
}
