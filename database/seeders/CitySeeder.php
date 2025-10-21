<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Governorate;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Saudi Arabia Cities
        $riyadh = Governorate::whereTranslation('name', 'Riyadh', 'en')->first();
        $saudiCities = [
            'Riyadh' => [
                ['en' => 'Riyadh', 'ar' => 'الرياض'],
                ['en' => 'Diriyah', 'ar' => 'الدرعية'],
                ['en' => 'Al-Kharj', 'ar' => 'الخرج'],
                ['en' => 'Majmaah', 'ar' => 'المجمعة'],
                ['en' => 'Zulfi', 'ar' => 'الزلفي'],
            ],
            'Mecca' => [
                ['en' => 'Mecca', 'ar' => 'مكة المكرمة'],
                ['en' => 'Jeddah', 'ar' => 'جدة'],
                ['en' => 'Taif', 'ar' => 'الطائف'],
                ['en' => 'Yanbu', 'ar' => 'ينبع'],
            ],
            'Medina' => [
                ['en' => 'Medina', 'ar' => 'المدينة المنورة'],
                ['en' => 'Yanbu Al-Bahr', 'ar' => 'ينبع البحر'],
            ],
            'Eastern Province' => [
                ['en' => 'Dammam', 'ar' => 'الدمام'],
                ['en' => 'Khobar', 'ar' => 'الخبر'],
                ['en' => 'Dhahran', 'ar' => 'الظهران'],
                ['en' => 'Hafar Al-Batin', 'ar' => 'حفر الباطن'],
                ['en' => 'Jubail', 'ar' => 'الجبيل'],
            ],
        ];

        foreach ($saudiCities as $govEn => $cities) {
            $governorate = Governorate::whereTranslation('name', $govEn, 'en')->first();
            if ($governorate) {
                foreach ($cities as $city) {
                    $cityModel = City::create([
                        'governorate_id' => $governorate->id,
                    ]);

                    $cityModel->translateOrNew('en')->name = $city['en'];
                    $cityModel->translateOrNew('ar')->name = $city['ar'];
                    $cityModel->save();
                }
            }
        }

        // UAE Cities
        $uaeCities = [
            'Dubai' => [
                ['en' => 'Dubai', 'ar' => 'دبي'],
                ['en' => 'Deira', 'ar' => 'ديرة'],
                ['en' => 'Bur Dubai', 'ar' => 'بر دبي'],
            ],
            'Abu Dhabi' => [
                ['en' => 'Abu Dhabi', 'ar' => 'أبو ظبي'],
                ['en' => 'Al Ain', 'ar' => 'العين'],
                ['en' => 'Al Dhafra', 'ar' => 'الظفرة'],
            ],
            'Sharjah' => [
                ['en' => 'Sharjah', 'ar' => 'الشارقة'],
                ['en' => 'Al Dhaid', 'ar' => 'الذيد'],
            ],
        ];

        foreach ($uaeCities as $govEn => $cities) {
            $governorate = Governorate::whereTranslation('name', $govEn, 'en')->first();
            if ($governorate) {
                foreach ($cities as $city) {
                    $cityModel = City::create([
                        'governorate_id' => $governorate->id,
                    ]);

                    $cityModel->translateOrNew('en')->name = $city['en'];
                    $cityModel->translateOrNew('ar')->name = $city['ar'];
                    $cityModel->save();
                }
            }
        }

        // Jordan Cities
        $jordanCities = [
            'Amman' => [
                ['en' => 'Amman', 'ar' => 'عمّان'],
                ['en' => 'Wadi as-Ser', 'ar' => 'وادي السير'],
                ['en' => 'Sahab', 'ar' => 'سحاب'],
                ['en' => 'Al-Jubeiha', 'ar' => 'الجبيلة'],
                ['en' => 'Abdali', 'ar' => 'عبدلي'],
            ],
            'Irbid' => [
                ['en' => 'Irbid', 'ar' => 'إربد'],
                ['en' => 'Ramtha', 'ar' => 'الرمثا'],
                ['en' => 'Al-Husun', 'ar' => 'الحصن'],
            ],
            'Zarqa' => [
                ['en' => 'Zarqa', 'ar' => 'الزرقاء'],
                ['en' => 'Russeifa', 'ar' => 'الرصيفة'],
                ['en' => 'Azraq', 'ar' => 'الأزرق'],
            ],
            'Aqaba' => [
                ['en' => 'Aqaba', 'ar' => 'العقبة'],
                ['en' => 'Wadi Rum', 'ar' => 'وادي رم'],
            ],
            'Balqa' => [
                ['en' => 'Salt', 'ar' => 'السلط'],
            ],
            'Mafraq' => [
                ['en' => 'Mafraq', 'ar' => 'المفرق'],
            ],
            'Jerash' => [
                ['en' => 'Jerash', 'ar' => 'جرش'],
            ],
            'Ajloun' => [
                ['en' => 'Ajloun', 'ar' => 'عجلون'],
            ],
            'Madaba' => [
                ['en' => 'Madaba', 'ar' => 'مأدبا'],
            ],
            'Karak' => [
                ['en' => 'Karak', 'ar' => 'الكرك'],
            ],
            'Tafilah' => [
                ['en' => 'Tafilah', 'ar' => 'الطفيلة'],
            ],
            'Ma\'an' => [
                ['en' => 'Ma\'an', 'ar' => 'معان'],
            ],
        ];

        foreach ($jordanCities as $govEn => $cities) {
            $governorate = Governorate::whereTranslation('name', $govEn, 'en')->first();
            if ($governorate) {
                foreach ($cities as $city) {
                    $cityModel = City::create([
                        'governorate_id' => $governorate->id,
                    ]);

                    $cityModel->translateOrNew('en')->name = $city['en'];
                    $cityModel->translateOrNew('ar')->name = $city['ar'];
                    $cityModel->save();
                }
            }
        }

        // Egypt Cities
        $egyptCities = [
            'Cairo' => [
                ['en' => 'Cairo', 'ar' => 'القاهرة'],
                ['en' => 'Giza', 'ar' => 'الجيزة'],
                ['en' => 'Helwan', 'ar' => 'حلوان'],
                ['en' => 'New Cairo', 'ar' => 'القاهرة الجديدة'],
            ],
            'Alexandria' => [
                ['en' => 'Alexandria', 'ar' => 'الإسكندرية'],
                ['en' => 'Borg El Arab', 'ar' => 'برج العرب'],
            ],
            'Giza' => [
                ['en' => 'Giza', 'ar' => 'الجيزة'],
                ['en' => '6th of October City', 'ar' => 'السادس من أكتوبر'],
                ['en' => 'Sheikh Zayed City', 'ar' => 'مدينة الشيخ زايد'],
            ],
        ];

        foreach ($egyptCities as $govEn => $cities) {
            $governorate = Governorate::whereTranslation('name', $govEn, 'en')->first();
            if ($governorate) {
                foreach ($cities as $city) {
                    $cityModel = City::create([
                        'governorate_id' => $governorate->id,
                    ]);

                    $cityModel->translateOrNew('en')->name = $city['en'];
                    $cityModel->translateOrNew('ar')->name = $city['ar'];
                    $cityModel->save();
                }
            }
        }
    }
}
