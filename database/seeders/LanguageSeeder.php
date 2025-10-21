<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
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
            [
                'name' => 'French',
                'code' => 'fr',
                'native_name' => 'Français',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => false,
            ],
            [
                'name' => 'Spanish',
                'code' => 'es',
                'native_name' => 'Español',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => false,
            ],
            [
                'name' => 'Chinese',
                'code' => 'zh',
                'native_name' => '中文',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => false,
            ],
            [
                'name' => 'Urdu',
                'code' => 'ur',
                'native_name' => 'اردو',
                'direction' => 'rtl',
                'is_default' => false,
                'is_active' => false,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate($language);
        }
    }
}
