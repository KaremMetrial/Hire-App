<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'code' => 'SA',
                'is_active' => true,
            ],
            [
                'code' => 'AE',
                'is_active' => true,
            ],
            [
                'code' => 'JO',
                'is_active' => true,
            ],
            [
                'code' => 'EG',
                'is_active' => true,
            ],
            [
                'code' => 'QA',
                'is_active' => true,
            ],
            [
                'code' => 'KW',
                'is_active' => true,
            ],
            [
                'code' => 'BH',
                'is_active' => true,
            ],
            [
                'code' => 'OM',
                'is_active' => true,
            ],
            [
                'code' => 'US',
                'is_active' => false,
            ],
            [
                'code' => 'GB',
                'is_active' => false,
            ],
        ];

        $translations = [
            'SA' => [
                'en' => 'Saudi Arabia',
                'ar' => 'المملكة العربية السعودية',
            ],
            'AE' => [
                'en' => 'United Arab Emirates',
                'ar' => 'الإمارات العربية المتحدة',
            ],
            'JO' => [
                'en' => 'Jordan',
                'ar' => 'الأردن',
            ],
            'EG' => [
                'en' => 'Egypt',
                'ar' => 'مصر',
            ],
            'QA' => [
                'en' => 'Qatar',
                'ar' => 'قطر',
            ],
            'KW' => [
                'en' => 'Kuwait',
                'ar' => 'الكويت',
            ],
            'BH' => [
                'en' => 'Bahrain',
                'ar' => 'البحرين',
            ],
            'OM' => [
                'en' => 'Oman',
                'ar' => 'عمان',
            ],
            'US' => [
                'en' => 'United States',
                'ar' => 'الولايات المتحدة',
            ],
            'GB' => [
                'en' => 'United Kingdom',
                'ar' => 'المملكة المتحدة',
            ],
        ];

        foreach ($countries as $countryData) {
            $country = Country::create($countryData);

            // Add translations
            $countryTranslations = $translations[$countryData['code']];
            foreach ($countryTranslations as $locale => $name) {
                $country->translateOrNew($locale)->name = $name;
            }

            $country->save();
        }
    }

}
