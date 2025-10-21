<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $customerTypes = [
            [
                'is_active' => true,
                'en' => ['name' => 'Individual'],
                'ar' => ['name' => 'فردي'],
            ],
            [
                'is_active' => true,
                'en' => ['name' => 'Corporate'],
                'ar' => ['name' => 'شركات'],
            ],
            [
                'is_active' => true,
                'en' => ['name' => 'Tourist'],
                'ar' => ['name' => 'سائح'],
            ],
            [
                'is_active' => true,
                'en' => ['name' => 'Student'],
                'ar' => ['name' => 'طالب'],
            ],
            [
                'is_active' => true,
                'en' => ['name' => 'Government Employee'],
                'ar' => ['name' => 'موظف حكومي'],
            ],
            [
                'is_active' => true,
                'en' => ['name' => 'Expatriate'],
                'ar' => ['name' => 'مقيم'],
            ],
        ];

        foreach ($customerTypes as $customerType) {
            $customerTypeModel = CustomerType::create([
                'is_active' => $customerType['is_active'],
            ]);

            $customerTypeModel->translateOrNew('en')->name = $customerType['en']['name'];
            $customerTypeModel->translateOrNew('ar')->name = $customerType['ar']['name'];
            $customerTypeModel->save();
        }
    }
}
