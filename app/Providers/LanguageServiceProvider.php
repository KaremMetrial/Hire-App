<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Repositories\LanguageRepository;

class LanguageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LanguageRepository::class, function () {
            return new LanguageRepository();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(LanguageRepository $languageRepository): void
    {
        if(Schema::hasTable('languages')) {
            $languages = $languageRepository->getAllCodes();

            if (empty($languages)) {
                $languages = ['en'];
            }

            Config::set('translatable.locales', $languages);
        }
    }
}
