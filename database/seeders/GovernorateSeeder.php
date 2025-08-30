<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Governorate;
use App\Models\Country;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countryId = Country::first()->id;
        $governorates = [
            ['en' => 'Amman', 'ar' => 'عمّان'],
            ['en' => 'Irbid', 'ar' => 'إربد'],
            ['en' => 'Zarqa', 'ar' => 'الزرقاء'],
            ['en' => 'Balqa', 'ar' => 'البلقاء'],
            ['en' => 'Mafraq', 'ar' => 'المفرق'],
            ['en' => 'Jerash', 'ar' => 'جرش'],
            ['en' => 'Ajloun', 'ar' => 'عجلون'],
            ['en' => 'Madaba', 'ar' => 'مأدبا'],
            ['en' => 'Karak', 'ar' => 'الكرك'],
            ['en' => 'Tafilah', 'ar' => 'الطفيلة'],
            ['en' => 'Ma\'an', 'ar' => 'معان'],
            ['en' => 'Aqaba', 'ar' => 'العقبة'],
        ];
        foreach ($governorates as $gov) {
            Governorate::create([
                'country_id' => $countryId,
                'en' => ['name' => $gov['en']],
                'ar' => ['name' => $gov['ar']],
            ]);
        }
    }
}
