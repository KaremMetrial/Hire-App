<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create main admin accounts
        $admins = [
            [
                'name' => 'Kareem Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('123456789'),
                'phone' => '+966-50-123-4567',
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0001',
            ],
            [
                'name' => 'Support Manager',
                'email' => 'support@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0002',
            ],
            [
                'name' => 'Operations Admin',
                'email' => 'operations@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0003',
            ],
            [
                'name' => 'Finance Admin',
                'email' => 'finance@hireapp.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0004',
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }

        // Create additional random admins
        Admin::factory()->count(5)->create();
    }
}
