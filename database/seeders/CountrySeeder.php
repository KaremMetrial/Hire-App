<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Country;

    class CountrySeeder extends Seeder
    {
        public function run(): void
        {
            Country::create([
                'code' => 'JO',
                'is_active' => true,
                'en' => ['name' => 'Jordan'],
                'ar' => ['name' => 'الأردن'],
            ]);
        }
    }
