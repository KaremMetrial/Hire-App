<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\City;
    use App\Models\Governorate;

    class CitySeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            // المحافظات مع المدن
            $citiesByGovernorate = [
                'Amman' => [
                    ['en' => 'Amman', 'ar' => 'عمّان'],
                    ['en' => 'Wadi as-Ser', 'ar' => 'وادي السير'],
                    ['en' => 'Sahab', 'ar' => 'سحاب'],
                    ['en' => 'Al-Jubeiha', 'ar' => 'الجبيلة'],
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

            foreach ($citiesByGovernorate as $govEn => $cities) {
                $governorate = Governorate::whereTranslation('name', $govEn, 'en')->first();

                if ($governorate) {
                    foreach ($cities as $city) {
                        City::create([
                            'governorate_id' => $governorate->id,
                            'en' => ['name' => $city['en']],
                            'ar' => ['name' => $city['ar']],
                        ]);
                    }
                }
            }
        }
    }
