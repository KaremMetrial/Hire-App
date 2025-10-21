<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Fuel;
use App\Models\RentalShop;
use App\Models\Transmission;
use App\Models\City;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        // Get rental shops
        $premiumRiyadh = RentalShop::where('name', 'Premium Car Rentals - Riyadh')->first();
        $cityAutoDubai = RentalShop::where('name', 'City Auto Hire - Dubai')->first();
        $luxuryJeddah = RentalShop::where('name', 'Luxury Fleet - Jeddah')->first();
        $premiumAmman = RentalShop::where('name', 'Premium Car Rentals - Amman')->first();
        $cityAutoDammam = RentalShop::where('name', 'City Auto Hire - Dammam')->first();

        // Get cities using a more reliable approach
        $allCities = City::with('translations')->get();
        $riyadh = $allCities->firstWhere('translations.0.name', 'Riyadh');
        $dubai = $allCities->firstWhere('translations.0.name', 'Dubai');
        $jeddah = $allCities->firstWhere('translations.0.name', 'Jeddah');
        $amman = $allCities->firstWhere('translations.0.name', 'Amman');
        $dammam = $allCities->firstWhere('translations.0.name', 'Dammam');

        // Get car models using a more reliable approach
        $allModels = CarModel::with('translations')->get();
        $camry = $allModels->firstWhere('translations.0.name', 'Camry');
        $corolla = $allModels->firstWhere('translations.0.name', 'Corolla');
        $civic = $allModels->firstWhere('translations.0.name', 'Civic');
        $accord = $allModels->firstWhere('translations.0.name', 'Accord');
        $sentra = $allModels->firstWhere('translations.0.name', 'Sentra');
        $altima = $allModels->firstWhere('translations.0.name', 'Altima');
        $series3 = $allModels->firstWhere('translations.0.name', '3 Series');
        $series5 = $allModels->firstWhere('translations.0.name', '5 Series');
        $cClass = $allModels->firstWhere('translations.0.name', 'C-Class');
        $eClass = $allModels->firstWhere('translations.0.name', 'E-Class');
        $x5 = $allModels->firstWhere('translations.0.name', 'X5');
        $gle = $allModels->firstWhere('translations.0.name', 'GLE');
        $tucson = $allModels->firstWhere('translations.0.name', 'Tucson');
        $sorento = $allModels->firstWhere('translations.0.name', 'Sorento');
        $mustang = $allModels->firstWhere('translations.0.name', 'Mustang');
        $f150 = $allModels->firstWhere('translations.0.name', 'F-150');

        // Debug: Check which models were found
        $models = [
            'camry' => $camry,
            'corolla' => $corolla,
            'civic' => $civic,
            'accord' => $accord,
            'sentra' => $sentra,
            'altima' => $altima,
            'series3' => $series3,
            'series5' => $series5,
            'cClass' => $cClass,
            'eClass' => $eClass,
            'x5' => $x5,
            'gle' => $gle,
            'tucson' => $tucson,
            'sorento' => $sorento,
            'mustang' => $mustang,
            'f150' => $f150,
        ];

        foreach ($models as $name => $model) {
            if (!$model) {
                $this->command->warn("Model not found: {$name}");
            }
        }

        // Get categories
        $sedan = Category::whereTranslation('name', 'Sedan', 'en')->first();
        $suv = Category::whereTranslation('name', 'SUV', 'en')->first();
        $luxury = Category::whereTranslation('name', 'Luxury', 'en')->first();
        $pickup = Category::whereTranslation('name', 'Pickup', 'en')->first();
        $sports = Category::whereTranslation('name', 'Sports', 'en')->first();

        // Get fuel types
        $gasoline = Fuel::whereTranslation('name', 'Gasoline', 'en')->first();
        $diesel = Fuel::whereTranslation('name', 'Diesel', 'en')->first();
        $hybrid = Fuel::whereTranslation('name', 'Hybrid', 'en')->first();

        // Get transmissions
        $automatic = Transmission::whereTranslation('name', 'Automatic', 'en')->first();
        $manual = Transmission::whereTranslation('name', 'Manual', 'en')->first();

        $cars = [
            // Premium Riyadh Cars
            [
                'rental_shop_id' => $premiumRiyadh->id,
                'model_id' => $camry->id,
                'category_id' => $sedan->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $riyadh->id,
                'year_of_manufacture' => 2023,
                'color' => 'Pearl White',
                'license_plate' => 'SA-12345',
                'num_of_seat' => 5,
                'kilometers' => 15000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],
            [
                'rental_shop_id' => $premiumRiyadh->id,
                'model_id' => $corolla->id,
                'category_id' => $sedan->id,
                'fuel_id' => $hybrid->id,
                'transmission_id' => $automatic->id,
                'city_id' => $riyadh->id,
                'year_of_manufacture' => 2024,
                'color' => 'Metallic Gray',
                'license_plate' => 'SA-12346',
                'num_of_seat' => 5,
                'kilometers' => 8000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],
            [
                'rental_shop_id' => $premiumRiyadh->id,
                'model_id' => $highlander->id ?? null,
                'category_id' => $suv->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $riyadh->id,
                'year_of_manufacture' => 2023,
                'color' => 'Midnight Black',
                'license_plate' => 'SA-12347',
                'num_of_seat' => 7,
                'kilometers' => 12000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 25, valid license required',
            ],

            // City Auto Dubai Cars
            [
                'rental_shop_id' => $cityAutoDubai->id,
                'model_id' => $civic->id,
                'category_id' => $sedan->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $dubai->id,
                'year_of_manufacture' => 2023,
                'color' => 'Sport Blue',
                'license_plate' => 'DXB-98765',
                'num_of_seat' => 5,
                'kilometers' => 20000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],
            [
                'rental_shop_id' => $cityAutoDubai->id,
                'model_id' => $tucson->id,
                'category_id' => $suv->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $dubai->id,
                'year_of_manufacture' => 2024,
                'color' => 'Desert Sand',
                'license_plate' => 'DXB-98766',
                'num_of_seat' => 5,
                'kilometers' => 10000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],

            // Luxury Fleet Jeddah Cars
            [
                'rental_shop_id' => $luxuryJeddah->id,
                'model_id' => $series5->id,
                'category_id' => $luxury->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $jeddah->id,
                'year_of_manufacture' => 2024,
                'color' => 'Phantom Black',
                'license_plate' => 'JED-55555',
                'num_of_seat' => 5,
                'kilometers' => 5000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 27, premium license required',
            ],
            [
                'rental_shop_id' => $luxuryJeddah->id,
                'model_id' => $gle->id,
                'category_id' => $luxury->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $jeddah->id,
                'year_of_manufacture' => 2023,
                'color' => 'Diamond Silver',
                'license_plate' => 'JED-55556',
                'num_of_seat' => 7,
                'kilometers' => 8000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 27, premium license required',
            ],

            // Premium Amman Cars
            [
                'rental_shop_id' => $premiumAmman->id,
                'model_id' => $accord->id,
                'category_id' => $sedan->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $amman->id,
                'year_of_manufacture' => 2023,
                'color' => 'Crystal White',
                'license_plate' => 'JO-11111',
                'num_of_seat' => 5,
                'kilometers' => 18000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],
            [
                'rental_shop_id' => $premiumAmman->id,
                'model_id' => $sentra->id,
                'category_id' => $sedan->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $amman->id,
                'year_of_manufacture' => 2024,
                'color' => 'Sunset Orange',
                'license_plate' => 'JO-11112',
                'num_of_seat' => 5,
                'kilometers' => 7000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],

            // City Auto Dammam Cars
            [
                'rental_shop_id' => $cityAutoDammam->id,
                'model_id' => $altima->id,
                'category_id' => $sedan->id,
                'fuel_id' => $gasoline->id,
                'transmission_id' => $automatic->id,
                'city_id' => $dammam->id,
                'year_of_manufacture' => 2023,
                'color' => 'Navy Blue',
                'license_plate' => 'DM-22222',
                'num_of_seat' => 5,
                'kilometers' => 25000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],
            [
                'rental_shop_id' => $cityAutoDammam->id,
                'model_id' => $sorento->id,
                'category_id' => $suv->id,
                'fuel_id' => $diesel->id,
                'transmission_id' => $automatic->id,
                'city_id' => $dammam->id,
                'year_of_manufacture' => 2024,
                'color' => 'Forest Green',
                'license_plate' => 'DM-22223',
                'num_of_seat' => 7,
                'kilometers' => 12000,
                'is_active' => true,
                'rental_shop_rule' => 'Minimum age 21, valid license required',
            ],
        ];

        foreach ($cars as $carData) {
            // Skip if model_id is null
            if (!$carData['model_id']) {
                $this->command->warn('Skipping car with null model_id for rental shop: ' . $carData['rental_shop_id']);
                continue;
            }
            Car::create($carData);
        }
    }
}
