<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Database\Seeder;

class CarModelSeeder extends Seeder
{
    public function run(): void
    {
        $toyota = Brand::whereTranslation('name', 'Toyota', 'en')->first();
        $honda = Brand::whereTranslation('name', 'Honda', 'en')->first();
        $nissan = Brand::whereTranslation('name', 'Nissan', 'en')->first();
        $bmw = Brand::whereTranslation('name', 'BMW', 'en')->first();
        $mercedes = Brand::whereTranslation('name', 'Mercedes-Benz', 'en')->first();
        $audi = Brand::whereTranslation('name', 'Audi', 'en')->first();
        $ford = Brand::whereTranslation('name', 'Ford', 'en')->first();
        $hyundai = Brand::whereTranslation('name', 'Hyundai', 'en')->first();
        $kia = Brand::whereTranslation('name', 'Kia', 'en')->first();
        $chevrolet = Brand::whereTranslation('name', 'Chevrolet', 'en')->first();
        $volkswagen = Brand::whereTranslation('name', 'Volkswagen', 'en')->first();
        $ferrari = Brand::whereTranslation('name', 'Ferrari', 'en')->first();
        $lamborghini = Brand::whereTranslation('name', 'Lamborghini', 'en')->first();
        $mazda = Brand::whereTranslation('name', 'Mazda', 'en')->first();
        $mitsubishi = Brand::whereTranslation('name', 'Mitsubishi', 'en')->first();

        $models = [
            // Toyota
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'Camry'], 'ar' => ['name' => 'كامري']],
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'Corolla'], 'ar' => ['name' => 'كورولا']],
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'Yaris'], 'ar' => ['name' => 'ياريس']],
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'RAV4'], 'ar' => ['name' => 'راف 4']],
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'Highlander'], 'ar' => ['name' => 'هايلاندر']],
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'Land Cruiser'], 'ar' => ['name' => 'لاند كروزر']],
            ['brand_id' => $toyota->id, 'is_active' => true, 'en' => ['name' => 'Hilux'], 'ar' => ['name' => 'هايلكس']],

            // Honda
            ['brand_id' => $honda->id, 'is_active' => true, 'en' => ['name' => 'Civic'], 'ar' => ['name' => 'سيفيك']],
            ['brand_id' => $honda->id, 'is_active' => true, 'en' => ['name' => 'Accord'], 'ar' => ['name' => 'أكورد']],
            ['brand_id' => $honda->id, 'is_active' => true, 'en' => ['name' => 'CR-V'], 'ar' => ['name' => 'سي آر في']],
            ['brand_id' => $honda->id, 'is_active' => true, 'en' => ['name' => 'HR-V'], 'ar' => ['name' => 'إتش آر في']],
            ['brand_id' => $honda->id, 'is_active' => true, 'en' => ['name' => 'Pilot'], 'ar' => ['name' => 'بايلوت']],

            // Nissan
            ['brand_id' => $nissan->id, 'is_active' => true, 'en' => ['name' => 'Sentra'], 'ar' => ['name' => 'سنترا']],
            ['brand_id' => $nissan->id, 'is_active' => true, 'en' => ['name' => 'Altima'], 'ar' => ['name' => 'ألتيما']],
            ['brand_id' => $nissan->id, 'is_active' => true, 'en' => ['name' => 'Patrol'], 'ar' => ['name' => 'باترول']],
            ['brand_id' => $nissan->id, 'is_active' => true, 'en' => ['name' => 'X-Trail'], 'ar' => ['name' => 'إكس تريل']],
            ['brand_id' => $nissan->id, 'is_active' => true, 'en' => ['name' => 'Rogue'], 'ar' => ['name' => 'روغ']],

            // BMW
            ['brand_id' => $bmw->id, 'is_active' => true, 'en' => ['name' => '3 Series'], 'ar' => ['name' => 'الفئة الثالثة']],
            ['brand_id' => $bmw->id, 'is_active' => true, 'en' => ['name' => '5 Series'], 'ar' => ['name' => 'الفئة الخامسة']],
            ['brand_id' => $bmw->id, 'is_active' => true, 'en' => ['name' => '7 Series'], 'ar' => ['name' => 'الفئة السابعة']],
            ['brand_id' => $bmw->id, 'is_active' => true, 'en' => ['name' => 'X3'], 'ar' => ['name' => 'إكس 3']],
            ['brand_id' => $bmw->id, 'is_active' => true, 'en' => ['name' => 'X5'], 'ar' => ['name' => 'إكس 5']],
            ['brand_id' => $bmw->id, 'is_active' => true, 'en' => ['name' => 'X6'], 'ar' => ['name' => 'إكس 6']],

            // Mercedes
            ['brand_id' => $mercedes->id, 'is_active' => true, 'en' => ['name' => 'C-Class'], 'ar' => ['name' => 'الفئة C']],
            ['brand_id' => $mercedes->id, 'is_active' => true, 'en' => ['name' => 'E-Class'], 'ar' => ['name' => 'الفئة E']],
            ['brand_id' => $mercedes->id, 'is_active' => true, 'en' => ['name' => 'S-Class'], 'ar' => ['name' => 'الفئة S']],
            ['brand_id' => $mercedes->id, 'is_active' => true, 'en' => ['name' => 'GLC'], 'ar' => ['name' => 'جي إل سي']],
            ['brand_id' => $mercedes->id, 'is_active' => true, 'en' => ['name' => 'GLE'], 'ar' => ['name' => 'جي إل إي']],
            ['brand_id' => $mercedes->id, 'is_active' => true, 'en' => ['name' => 'G-Class'], 'ar' => ['name' => 'الفئة G']],

            // Audi
            ['brand_id' => $audi->id, 'is_active' => true, 'en' => ['name' => 'A4'], 'ar' => ['name' => 'A4']],
            ['brand_id' => $audi->id, 'is_active' => true, 'en' => ['name' => 'A6'], 'ar' => ['name' => 'A6']],
            ['brand_id' => $audi->id, 'is_active' => true, 'en' => ['name' => 'Q5'], 'ar' => ['name' => 'كيو 5']],
            ['brand_id' => $audi->id, 'is_active' => true, 'en' => ['name' => 'Q7'], 'ar' => ['name' => 'كيو 7']],

            // Ford
            ['brand_id' => $ford->id, 'is_active' => true, 'en' => ['name' => 'Mustang'], 'ar' => ['name' => 'موستانغ']],
            ['brand_id' => $ford->id, 'is_active' => true, 'en' => ['name' => 'F-150'], 'ar' => ['name' => 'F-150']],
            ['brand_id' => $ford->id, 'is_active' => true, 'en' => ['name' => 'Explorer'], 'ar' => ['name' => 'إكسبلورر']],
            ['brand_id' => $ford->id, 'is_active' => true, 'en' => ['name' => 'Edge'], 'ar' => ['name' => 'إيدج']],

            // Hyundai
            ['brand_id' => $hyundai->id, 'is_active' => true, 'en' => ['name' => 'Elantra'], 'ar' => ['name' => 'إلنترا']],
            ['brand_id' => $hyundai->id, 'is_active' => true, 'en' => ['name' => 'Sonata'], 'ar' => ['name' => 'سوناتا']],
            ['brand_id' => $hyundai->id, 'is_active' => true, 'en' => ['name' => 'Tucson'], 'ar' => ['name' => 'توسان']],
            ['brand_id' => $hyundai->id, 'is_active' => true, 'en' => ['name' => 'Santa Fe'], 'ar' => ['name' => 'سانتا في']],
            ['brand_id' => $hyundai->id, 'is_active' => true, 'en' => ['name' => 'Palisade'], 'ar' => ['name' => 'باليسيد']],

            // Kia
            ['brand_id' => $kia->id, 'is_active' => true, 'en' => ['name' => 'Rio'], 'ar' => ['name' => 'ريو']],
            ['brand_id' => $kia->id, 'is_active' => true, 'en' => ['name' => 'Forte'], 'ar' => ['name' => 'فورتي']],
            ['brand_id' => $kia->id, 'is_active' => true, 'en' => ['name' => 'Sportage'], 'ar' => ['name' => 'سبورتاج']],
            ['brand_id' => $kia->id, 'is_active' => true, 'en' => ['name' => 'Sorento'], 'ar' => ['name' => 'سورينتو']],
            ['brand_id' => $kia->id, 'is_active' => true, 'en' => ['name' => 'Telluride'], 'ar' => ['name' => 'تيلورايد']],

            // Chevrolet
            ['brand_id' => $chevrolet->id, 'is_active' => true, 'en' => ['name' => 'Camaro'], 'ar' => ['name' => 'كامارو']],
            ['brand_id' => $chevrolet->id, 'is_active' => true, 'en' => ['name' => 'Corvette'], 'ar' => ['name' => 'كورفيت']],
            ['brand_id' => $chevrolet->id, 'is_active' => true, 'en' => ['name' => 'Tahoe'], 'ar' => ['name' => 'تاهو']],
            ['brand_id' => $chevrolet->id, 'is_active' => true, 'en' => ['name' => 'Suburban'], 'ar' => ['name' => 'سابرban']],

            // Volkswagen
            ['brand_id' => $volkswagen->id, 'is_active' => true, 'en' => ['name' => 'Golf'], 'ar' => ['name' => 'غولف']],
            ['brand_id' => $volkswagen->id, 'is_active' => true, 'en' => ['name' => 'Passat'], 'ar' => ['name' => 'باسات']],
            ['brand_id' => $volkswagen->id, 'is_active' => true, 'en' => ['name' => 'Tiguan'], 'ar' => ['name' => 'تيغوان']],
            ['brand_id' => $volkswagen->id, 'is_active' => true, 'en' => ['name' => 'Atlas'], 'ar' => ['name' => 'أطلس']],

            // Ferrari
            ['brand_id' => $ferrari->id, 'is_active' => true, 'en' => ['name' => '488'], 'ar' => ['name' => '488']],
            ['brand_id' => $ferrari->id, 'is_active' => true, 'en' => ['name' => 'F8'], 'ar' => ['name' => 'F8']],
            ['brand_id' => $ferrari->id, 'is_active' => true, 'en' => ['name' => 'Roma'], 'ar' => ['name' => 'روما']],

            // Lamborghini
            ['brand_id' => $lamborghini->id, 'is_active' => true, 'en' => ['name' => 'Huracan'], 'ar' => ['name' => 'هوراكان']],
            ['brand_id' => $lamborghini->id, 'is_active' => true, 'en' => ['name' => 'Urus'], 'ar' => ['name' => 'أوروس']],
            ['brand_id' => $lamborghini->id, 'is_active' => true, 'en' => ['name' => 'Aventador'], 'ar' => ['name' => 'أفينتادور']],

            // Mazda
            ['brand_id' => $mazda->id, 'is_active' => true, 'en' => ['name' => 'Mazda3'], 'ar' => ['name' => 'مازدا3']],
            ['brand_id' => $mazda->id, 'is_active' => true, 'en' => ['name' => 'Mazda6'], 'ar' => ['name' => 'مازدا6']],
            ['brand_id' => $mazda->id, 'is_active' => true, 'en' => ['name' => 'CX-5'], 'ar' => ['name' => 'سي إكس 5']],
            ['brand_id' => $mazda->id, 'is_active' => true, 'en' => ['name' => 'CX-9'], 'ar' => ['name' => 'سي إكس 9']],

            // Mitsubishi
            ['brand_id' => $mitsubishi->id, 'is_active' => true, 'en' => ['name' => 'Lancer'], 'ar' => ['name' => 'لانسر']],
            ['brand_id' => $mitsubishi->id, 'is_active' => true, 'en' => ['name' => 'Outlander'], 'ar' => ['name' => 'أوتلاندر']],
            ['brand_id' => $mitsubishi->id, 'is_active' => true, 'en' => ['name' => 'Pajero'], 'ar' => ['name' => 'باجيرو']],
            ['brand_id' => $mitsubishi->id, 'is_active' => true, 'en' => ['name' => 'Montero'], 'ar' => ['name' => 'مونتيرو']],
        ];

        foreach ($models as $model) {
            $carModel = CarModel::create([
                'brand_id' => $model['brand_id'],
                'is_active' => $model['is_active'],
            ]);

            $carModel->translateOrNew('en')->name = $model['en']['name'];
            $carModel->translateOrNew('ar')->name = $model['ar']['name'];
            $carModel->save();
        }
    }
}
