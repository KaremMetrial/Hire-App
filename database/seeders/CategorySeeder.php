<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'icon' => '🚗',
                'en' => ['name' => 'Sedan'],
                'ar' => ['name' => 'سيدان'],
            ],
            [
                'icon' => '🚙',
                'en' => ['name' => 'SUV'],
                'ar' => ['name' => 'سيارة دفع رباعي'],
            ],
            [
                'icon' => '🚕',
                'en' => ['name' => 'Hatchback'],
                'ar' => ['name' => 'هاتشباك'],
            ],
            [
                'icon' => '🏎️',
                'en' => ['name' => 'Coupe'],
                'ar' => ['name' => 'كوبيه'],
            ],
            [
                'icon' => '🚘',
                'en' => ['name' => 'Convertible'],
                'ar' => ['name' => 'سيارة مكشوفة'],
            ],
            [
                'icon' => '🚐',
                'en' => ['name' => 'Minivan'],
                'ar' => ['name' => 'ميني فان'],
            ],
            [
                'icon' => '🚚',
                'en' => ['name' => 'Pickup'],
                'ar' => ['name' => 'شاحنة صغيرة'],
            ],
            [
                'icon' => '🌟',
                'en' => ['name' => 'Luxury'],
                'ar' => ['name' => 'سيارة فاخرة'],
            ],
            [
                'icon' => '🏎️',
                'en' => ['name' => 'Sports'],
                'ar' => ['name' => 'سيارة رياضية'],
            ],
            [
                'icon' => '🚌',
                'en' => ['name' => 'Van'],
                'ar' => ['name' => 'فان'],
            ],
            [
                'icon' => '⚡',
                'en' => ['name' => 'Electric'],
                'ar' => ['name' => 'سيارة كهربائية'],
            ],
            [
                'icon' => '🔋',
                'en' => ['name' => 'Hybrid'],
                'ar' => ['name' => 'سيارة هجينة'],
            ],
        ];

        foreach ($categories as $category) {
            $categoryModel = Category::create([
                'icon' => $category['icon'],
            ]);

            $categoryModel->translateOrNew('en')->name = $category['en']['name'];
            $categoryModel->translateOrNew('ar')->name = $category['ar']['name'];
            $categoryModel->save();
        }
    }
}
