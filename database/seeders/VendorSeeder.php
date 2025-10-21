<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        // Create specific vendor accounts
        $vendors = [
            [
                'name' => 'Premium Car Rentals',
                'email' => 'premium@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+966-50-111-2222',
                'national_id_photo' => 'https://via.placeholder.com/640x480/FF5722/FFFFFF?text=Premium+Car+Rentals+ID',
                'status' => 'approved',
                'actioned_at' => now(),
                'actioned_by' => 1,
            ],
            [
                'name' => 'City Auto Hire',
                'email' => 'cityauto@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+966-50-333-4444',
                'national_id_photo' => 'https://via.placeholder.com/640x480/4CAF50/FFFFFF?text=City+Auto+Hire+ID',
                'status' => 'approved',
                'actioned_at' => now(),
                'actioned_by' => 1,
            ],
            [
                'name' => 'Economy Wheels',
                'email' => 'economy@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+966-50-555-6666',
                'national_id_photo' => 'https://via.placeholder.com/640x480/2196F3/FFFFFF?text=Economy+Wheels+ID',
                'status' => 'pending',
            ],
            [
                'name' => 'Luxury Fleet',
                'email' => 'luxury@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+966-50-777-8888',
                'national_id_photo' => 'https://via.placeholder.com/640x480/9C27B0/FFFFFF?text=Luxury+Fleet+ID',
                'status' => 'approved',
                'actioned_at' => now(),
                'actioned_by' => 2,
            ],
            [
                'name' => 'Quick Rent',
                'email' => 'quick@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+966-50-999-0000',
                'national_id_photo' => 'https://via.placeholder.com/640x480/FF9800/FFFFFF?text=Quick+Rent+ID',
                'status' => 'rejected',
                'rejected_reason' => 'Incomplete documentation',
                'actioned_at' => now()->subDays(5),
                'actioned_by' => 1,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }

        // Create additional random vendors
        Vendor::factory()->count(15)->create();
    }
}
