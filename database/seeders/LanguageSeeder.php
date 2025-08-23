<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Languages Data
        $languages = [
            [
                'name' => 'English',
                'code' => 'en',
                'native_name' => 'English',
                'direction' => 'ltr',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Arabic',
                'code' => 'ar',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'is_default' => false,
                'is_active' => true,
            ],
        ];
        // Insert Languages
        foreach ($languages as $language) {
            Language::updateOrCreate($language);
        }
    }
}
