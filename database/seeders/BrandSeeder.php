<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'image' => 'https://via.placeholder.com/200x100/FF0000/FFFFFF?text=Toyota',
                'is_active' => true,
                'en' => ['name' => 'Toyota'],
                'ar' => ['name' => 'تويوتا'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/808080/FFFFFF?text=Honda',
                'is_active' => true,
                'en' => ['name' => 'Honda'],
                'ar' => ['name' => 'هوندا'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/0000FF/FFFFFF?text=Nissan',
                'is_active' => true,
                'en' => ['name' => 'Nissan'],
                'ar' => ['name' => 'نيسان'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/0066CC/FFFFFF?text=BMW',
                'is_active' => true,
                'en' => ['name' => 'BMW'],
                'ar' => ['name' => 'بي إم دبليو'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/000000/FFFFFF?text=Mercedes',
                'is_active' => true,
                'en' => ['name' => 'Mercedes-Benz'],
                'ar' => ['name' => 'مرسيدس بنز'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/FFD700/000000?text=Audi',
                'is_active' => true,
                'en' => ['name' => 'Audi'],
                'ar' => ['name' => 'أودي'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/003366/FFFFFF?text=Ford',
                'is_active' => true,
                'en' => ['name' => 'Ford'],
                'ar' => ['name' => 'فورد'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/FF6600/FFFFFF?text=Hyundai',
                'is_active' => true,
                'en' => ['name' => 'Hyundai'],
                'ar' => ['name' => 'هيونداي'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/0033CC/FFFFFF?text=Kia',
                'is_active' => true,
                'en' => ['name' => 'Kia'],
                'ar' => ['name' => 'كيا'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/CC0000/FFFFFF?text=Chevrolet',
                'is_active' => true,
                'en' => ['name' => 'Chevrolet'],
                'ar' => ['name' => 'شيفروليه'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/000000/FFFFFF?text=Volkswagen',
                'is_active' => true,
                'en' => ['name' => 'Volkswagen'],
                'ar' => ['name' => 'فولكس فاجن'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/FF0000/FFFFFF?text=Ferrari',
                'is_active' => true,
                'en' => ['name' => 'Ferrari'],
                'ar' => ['name' => 'فيراري'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/FFB300/000000?text=Lamborghini',
                'is_active' => true,
                'en' => ['name' => 'Lamborghini'],
                'ar' => ['name' => 'لامبورغيني'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/0099CC/FFFFFF?text=Mazda',
                'is_active' => true,
                'en' => ['name' => 'Mazda'],
                'ar' => ['name' => 'مازدا'],
            ],
            [
                'image' => 'https://via.placeholder.com/200x100/FF3300/FFFFFF?text=Mitsubishi',
                'is_active' => true,
                'en' => ['name' => 'Mitsubishi'],
                'ar' => ['name' => 'ميتسوبيشي'],
            ],
        ];

        foreach ($brands as $brand) {
            $brandModel = Brand::create([
                'image' => $brand['image'],
                'is_active' => $brand['is_active'],
            ]);

            $brandModel->translateOrNew('en')->name = $brand['en']['name'];
            $brandModel->translateOrNew('ar')->name = $brand['ar']['name'];
            $brandModel->save();
        }
    }
}
