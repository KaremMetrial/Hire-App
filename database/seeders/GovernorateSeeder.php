<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Governorate;
use App\Models\Country;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        // Saudi Arabia Governorates
        $saudiArabia = Country::where('code', 'SA')->first();
        $saudiGovernorates = [
            ['en' => 'Riyadh', 'ar' => 'الرياض'],
            ['en' => 'Mecca', 'ar' => 'مكة المكرمة'],
            ['en' => 'Medina', 'ar' => 'المدينة المنورة'],
            ['en' => 'Eastern Province', 'ar' => 'المنطقة الشرقية'],
            ['en' => 'Asir', 'ar' => 'عسير'],
            ['en' => 'Tabuk', 'ar' => 'تبوك'],
            ['en' => 'Hail', 'ar' => 'حائل'],
            ['en' => 'Northern Borders', 'ar' => 'الحدود الشمالية'],
            ['en' => 'Jizan', 'ar' => 'جازان'],
            ['en' => 'Najran', 'ar' => 'نجران'],
            ['en' => 'Al Bahah', 'ar' => 'الباحة'],
            ['en' => 'Al Jawf', 'ar' => 'الجوف'],
            ['en' => 'Al-Qassim', 'ar' => 'القصيم'],
        ];

        foreach ($saudiGovernorates as $gov) {
            $governorate = Governorate::create([
                'country_id' => $saudiArabia->id,
            ]);

            $governorate->translateOrNew('en')->name = $gov['en'];
            $governorate->translateOrNew('ar')->name = $gov['ar'];
            $governorate->save();
        }

        // UAE Emirates
        $uae = Country::where('code', 'AE')->first();
        $uaeGovernorates = [
            ['en' => 'Abu Dhabi', 'ar' => 'أبو ظبي'],
            ['en' => 'Dubai', 'ar' => 'دبي'],
            ['en' => 'Sharjah', 'ar' => 'الشارقة'],
            ['en' => 'Ajman', 'ar' => 'عجمان'],
            ['en' => 'Umm Al Quwain', 'ar' => 'أم القيوين'],
            ['en' => 'Ras Al Khaimah', 'ar' => 'رأس الخيمة'],
            ['en' => 'Fujairah', 'ar' => 'الفجيرة'],
        ];

        foreach ($uaeGovernorates as $gov) {
            $governorate = Governorate::create([
                'country_id' => $uae->id,
            ]);

            $governorate->translateOrNew('en')->name = $gov['en'];
            $governorate->translateOrNew('ar')->name = $gov['ar'];
            $governorate->save();
        }

        // Jordan Governorates
        $jordan = Country::where('code', 'JO')->first();
        $jordanGovernorates = [
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

        foreach ($jordanGovernorates as $gov) {
            $governorate = Governorate::create([
                'country_id' => $jordan->id,
            ]);

            $governorate->translateOrNew('en')->name = $gov['en'];
            $governorate->translateOrNew('ar')->name = $gov['ar'];
            $governorate->save();
        }

        // Egypt Governorates
        $egypt = Country::where('code', 'EG')->first();
        $egyptGovernorates = [
            ['en' => 'Cairo', 'ar' => 'القاهرة'],
            ['en' => 'Alexandria', 'ar' => 'الإسكندرية'],
            ['en' => 'Giza', 'ar' => 'الجيزة'],
            ['en' => 'Shubra El Kheima', 'ar' => 'شبرا الخيمة'],
            ['en' => 'Port Said', 'ar' => 'بورسعيد'],
            ['en' => 'Suez', 'ar' => 'السويس'],
            ['en' => 'Luxor', 'ar' => 'الأقصر'],
            ['en' => 'Aswan', 'ar' => 'أسوان'],
            ['en' => 'Mansoura', 'ar' => 'المنصورة'],
            ['en' => 'Tanta', 'ar' => 'طنطا'],
        ];

        foreach ($egyptGovernorates as $gov) {
            $governorate = Governorate::create([
                'country_id' => $egypt->id,
            ]);

            $governorate->translateOrNew('en')->name = $gov['en'];
            $governorate->translateOrNew('ar')->name = $gov['ar'];
            $governorate->save();
        }
    }
}
