<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run seeders in correct order to maintain foreign key constraints
        $this->call([
            // Language and Location Data (must come first)
            LanguageSeeder::class,
            CountrySeeder::class,
            GovernorateSeeder::class,
            CitySeeder::class,

            // User and Authentication Data
            AdminSeeder::class,
            UserSeeder::class,
            VendorSeeder::class,

            // Car-related Data
            BrandSeeder::class,
            CarModelSeeder::class,
            CategorySeeder::class,
            FuelSeeder::class,
            TransmissionSeeder::class,

            // Service Data
            ExtraServiceSeeder::class,
            InsuranceSeeder::class,
            CustomerTypeSeeder::class,
            DocumentSeeder::class,

            // Business Data
            RentalShopSeeder::class,
            CarSeeder::class,
            CarPriceSeeder::class,

            // Booking Data
            BookingSeeder::class,

            // Supporting Data
            NotificationSettingSeeder::class,
        ]);
    }
}
