<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::all();

        $models = [
            // Toyota Models
            'toyota' => [
                ['code' => 'camry', 'is_active' => true],
                ['code' => 'corolla', 'is_active' => true],
                ['code' => 'rav4', 'is_active' => true],
                ['code' => 'landcruiser', 'is_active' => true],
                ['code' => 'hilux', 'is_active' => true],
            ],
            // Honda Models
            'honda' => [
                ['code' => 'civic', 'is_active' => true],
                ['code' => 'accord', 'is_active' => true],
                ['code' => 'crv', 'is_active' => true],
            ],
            // Nissan Models
            'nissan' => [
                ['code' => 'sunny', 'is_active' => true],
                ['code' => 'xtrail', 'is_active' => true],
                ['code' => 'patrol', 'is_active' => true],
            ],
            // BMW Models
            'bmw' => [
                ['code' => '3series', 'is_active' => true],
                ['code' => '5series', 'is_active' => true],
                ['code' => 'x5', 'is_active' => true],
            ],
            // Mercedes Models
            'mercedes' => [
                ['code' => 'cclass', 'is_active' => true],
                ['code' => 'eclass', 'is_active' => true],
                ['code' => 'gle', 'is_active' => true],
            ],
        ];

        $modelNames = [
            // Toyota
            'camry' => ['en' => 'Camry', 'ar' => 'كامري'],
            'corolla' => ['en' => 'Corolla', 'ar' => 'كورولا'],
            'rav4' => ['en' => 'RAV4', 'ar' => 'راف فور'],
            'landcruiser' => ['en' => 'Land Cruiser', 'ar' => 'لاندكروزر'],
            'hilux' => ['en' => 'Hilux', 'ar' => 'هايلكس'],
            // Honda
            'civic' => ['en' => 'Civic', 'ar' => 'سيفيك'],
            'accord' => ['en' => 'Accord', 'ar' => 'أكورد'],
            'crv' => ['en' => 'CR-V', 'ar' => 'سي آر في'],
            // Nissan
            'sunny' => ['en' => 'Sunny', 'ar' => 'سنّي'],
            'xtrail' => ['en' => 'X-Trail', 'ar' => 'اكستريل'],
            'patrol' => ['en' => 'Patrol', 'ar' => 'باترول'],
            // BMW
            '3series' => ['en' => '3 Series', 'ar' => 'سيريس 3'],
            '5series' => ['en' => '5 Series', 'ar' => 'سيريس 5'],
            'x5' => ['en' => 'X5', 'ar' => 'اكس 5'],
            // Mercedes
            'cclass' => ['en' => 'C-Class', 'ar' => 'فئة سي'],
            'eclass' => ['en' => 'E-Class', 'ar' => 'فئة إي'],
            'gle' => ['en' => 'GLE', 'ar' => 'جي إل إي'],
        ];

        foreach ($brands as $brand) {
            if (isset($models[$brand->code])) {
                foreach ($models[$brand->code] as $model) {
                    $model['brand_id'] = $brand->id;
                    $createdModel = CarModel::create($model);

                    // Add translations
                    if (isset($modelNames[$model['code']])) {
                        $translations = $modelNames[$model['code']];
                        foreach ($translations as $locale => $name) {
                            $createdModel->translateOrNew($locale)->name = $name;
                        }
                        $createdModel->save();
                    }
                }
            }
        }
    }
}
