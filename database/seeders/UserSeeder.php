<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create specific test users
        $users = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0101',
                'birthday' => '1985-06-15',
                'face_license_id_photo' => 'https://via.placeholder.com/640x480/4CAF50/FFFFFF?text=John+License+Front',
                'back_license_id_photo' => 'https://via.placeholder.com/640x480/2196F3/FFFFFF?text=John+License+Back',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0102',
                'birthday' => '1990-03-22',
                'face_license_id_photo' => 'https://via.placeholder.com/640x480/E91E63/FFFFFF?text=Sarah+License+Front',
                'back_license_id_photo' => 'https://via.placeholder.com/640x480/9C27B0/FFFFFF?text=Sarah+License+Back',
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0103',
                'birthday' => '1988-11-08',
                'face_license_id_photo' => 'https://via.placeholder.com/640x480/FF9800/FFFFFF?text=Michael+License+Front',
                'back_license_id_photo' => 'https://via.placeholder.com/640x480/795548/FFFFFF?text=Michael+License+Back',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0104',
                'birthday' => '1992-07-30',
                'face_license_id_photo' => 'https://via.placeholder.com/640x480/00BCD4/FFFFFF?text=Emily+License+Front',
                'back_license_id_photo' => 'https://via.placeholder.com/640x480/009688/FFFFFF?text=Emily+License+Back',
            ],
            [
                'name' => 'Robert Wilson',
                'email' => 'robert.wilson@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0105',
                'birthday' => '1979-12-05',
                'face_license_id_photo' => 'https://via.placeholder.com/640x480/FFC107/FFFFFF?text=Robert+License+Front',
                'back_license_id_photo' => 'https://via.placeholder.com/640x480/FF5722/FFFFFF?text=Robert+License+Back',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // Create additional random users
        User::factory()->count(25)->create();
    }
}
