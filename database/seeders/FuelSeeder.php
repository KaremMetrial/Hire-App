<?php

namespace Database\Seeders;

use App\Models\Fuel;
use Illuminate\Database\Seeder;

class FuelSeeder extends Seeder
{
    public function run(): void
    {
        $fuels = [
            [
                'en' => ['name' => 'Gasoline'],
                'ar' => ['name' => 'بنزين'],
            ],
            [
                'en' => ['name' => 'Diesel'],
                'ar' => ['name' => 'ديزل'],
            ],
            [
                'en' => ['name' => 'Electric'],
                'ar' => ['name' => 'كهرباء'],
            ],
            [
                'en' => ['name' => 'Hybrid'],
                'ar' => ['name' => 'هجين'],
            ],
            [
                'en' => ['name' => 'Plug-in Hybrid'],
                'ar' => ['name' => 'هجين قابل للشحن'],
            ],
            [
                'en' => ['name' => 'LPG'],
                'ar' => ['name' => 'غاز البترول المسال'],
            ],
        ];

        foreach ($fuels as $fuel) {
            $fuelModel = Fuel::create();

            $fuelModel->translateOrNew('en')->name = $fuel['en']['name'];
            $fuelModel->translateOrNew('ar')->name = $fuel['ar']['name'];
            $fuelModel->save();
        }
    }
}
