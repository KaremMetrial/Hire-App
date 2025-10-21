<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\RentalShop;
use App\Models\Vendor;
use App\Models\Address;
use App\Models\WorkingDay;
use Illuminate\Database\Seeder;

class RentalShopSeeder extends Seeder
{
    public function run(): void
    {
        // Get vendors
        $premiumVendor = Vendor::where('email', 'premium@hireapp.com')->first();
        $cityAutoVendor = Vendor::where('email', 'cityauto@hireapp.com')->first();
        $luxuryVendor = Vendor::where('email', 'luxury@hireapp.com')->first();

        // Get cities
        $riyadh = City::whereTranslation('name', 'Riyadh', 'en')->first();
        $dubai = City::whereTranslation('name', 'Dubai', 'en')->first();
        $jeddah = City::whereTranslation('name', 'Jeddah', 'en')->first();
        $amman = City::whereTranslation('name', 'Amman', 'en')->first();
        $dammam = City::whereTranslation('name', 'Dammam', 'en')->first();

        $shops = [
            [
                'name' => 'Premium Car Rentals - Riyadh',
                'phone' => '+966-11-555-0001',
                'image' => 'https://via.placeholder.com/400x300/FF5722/FFFFFF?text=Premium+Riyadh',
                'is_active' => true,
                'status' => 'approved',
                'rating' => 4.8,
                'count_rating' => 156,
                'facebook_link' => 'https://facebook.com/premiumriyadh',
                'instagram_link' => 'https://instagram.com/premiumriyadh',
                'whatsapp_link' => 'https://wa.me/966115550001',
                'city_id' => $riyadh->id,
                'address' => 'King Fahd Road, Olaya District, Riyadh',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'vendors' => [$premiumVendor->id],
            ],
            [
                'name' => 'City Auto Hire - Dubai',
                'phone' => '+971-4-555-0002',
                'image' => 'https://via.placeholder.com/400x300/4CAF50/FFFFFF?text=City+Auto+Dubai',
                'is_active' => true,
                'status' => 'approved',
                'rating' => 4.6,
                'count_rating' => 89,
                'facebook_link' => 'https://facebook.com/cityautodubai',
                'instagram_link' => 'https://instagram.com/cityautodubai',
                'whatsapp_link' => 'https://wa.me/97145550002',
                'city_id' => $dubai->id,
                'address' => 'Sheikh Zayed Road, Dubai',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'vendors' => [$cityAutoVendor->id],
            ],
            [
                'name' => 'Luxury Fleet - Jeddah',
                'phone' => '+966-12-555-0003',
                'image' => 'https://via.placeholder.com/400x300/9C27B0/FFFFFF?text=Luxury+Fleet+Jeddah',
                'is_active' => true,
                'status' => 'approved',
                'rating' => 4.9,
                'count_rating' => 234,
                'facebook_link' => 'https://facebook.com/luxuryjeddah',
                'instagram_link' => 'https://instagram.com/luxuryjeddah',
                'whatsapp_link' => 'https://wa.me/966125550003',
                'city_id' => $jeddah->id,
                'address' => 'Prince Mohammed Road, Jeddah',
                'latitude' => 21.3891,
                'longitude' => 39.8579,
                'vendors' => [$luxuryVendor->id],
            ],
            [
                'name' => 'Premium Car Rentals - Amman',
                'phone' => '+962-6-555-0004',
                'image' => 'https://via.placeholder.com/400x300/FF5722/FFFFFF?text=Premium+Amman',
                'is_active' => true,
                'status' => 'approved',
                'rating' => 4.5,
                'count_rating' => 67,
                'facebook_link' => 'https://facebook.com/premiumamman',
                'instagram_link' => 'https://instagram.com/premiumamman',
                'whatsapp_link' => 'https://wa.me/96265550004',
                'city_id' => $amman->id,
                'address' => 'Abdali Boulevard, Amman',
                'latitude' => 31.9539,
                'longitude' => 35.9106,
                'vendors' => [$premiumVendor->id],
            ],
            [
                'name' => 'City Auto Hire - Dammam',
                'phone' => '+966-13-555-0005',
                'image' => 'https://via.placeholder.com/400x300/4CAF50/FFFFFF?text=City+Auto+Dammam',
                'is_active' => true,
                'status' => 'approved',
                'rating' => 4.4,
                'count_rating' => 45,
                'facebook_link' => 'https://facebook.com/cityautodammam',
                'instagram_link' => 'https://instagram.com/cityautodammam',
                'whatsapp_link' => 'https://wa.me/966135550005',
                'city_id' => $dammam->id,
                'address' => 'King Fahd Road, Dammam',
                'latitude' => 26.4262,
                'longitude' => 50.0888,
                'vendors' => [$cityAutoVendor->id],
            ],
        ];

        foreach ($shops as $shopData) {
            $vendors = $shopData['vendors'];
            $addressData = [
                'address' => $shopData['address'],
                'latitude' => $shopData['latitude'],
                'longitude' => $shopData['longitude'],
                'city_id' => $shopData['city_id'],
            ];

            unset($shopData['vendors'], $shopData['address'], $shopData['latitude'], $shopData['longitude'], $shopData['city_id']);

            $shop = RentalShop::create($shopData);

            // Attach vendors
            foreach ($vendors as $vendorId) {
                $shop->vendors()->attach($vendorId, ['role' => 'manager']);
            }

            // Create address
            Address::create([
                'addressable_type' => RentalShop::class,
                'addressable_id' => $shop->id,
                'address' => $addressData['address'],
                'latitude' => $addressData['latitude'],
                'longitude' => $addressData['longitude'],
                'city_id' => $addressData['city_id'],
                'country_id' => $addressData['city_id'] ? City::find($addressData['city_id'])->country_id : null,
            ]);

            // Create working days
            $workingDays = [
                ['day_of_week' => 1, 'open_time' => '08:00', 'close_time' => '20:00'], // Monday
                ['day_of_week' => 2, 'open_time' => '08:00', 'close_time' => '20:00'], // Tuesday
                ['day_of_week' => 3, 'open_time' => '08:00', 'close_time' => '20:00'], // Wednesday
                ['day_of_week' => 4, 'open_time' => '08:00', 'close_time' => '20:00'], // Thursday
                ['day_of_week' => 5, 'open_time' => '08:00', 'close_time' => '20:00'], // Friday
                ['day_of_week' => 6, 'open_time' => '09:00', 'close_time' => '18:00'], // Saturday
                ['day_of_week' => 7, 'open_time' => '09:00', 'close_time' => '18:00'], // Sunday
            ];

            foreach ($workingDays as $workingDay) {
                WorkingDay::create([
                    'rental_shop_id' => $shop->id,
                    'day_of_week' => $workingDay['day_of_week'],
                    'open_time' => $workingDay['open_time'],
                    'close_time' => $workingDay['close_time'],
                ]);
            }
        }
    }
}
