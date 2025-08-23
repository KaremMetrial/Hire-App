<?php

    namespace App\Providers;

    use App\Models\Language;
    use BezhanSalleh\LanguageSwitch\LanguageSwitch;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\ServiceProvider;
    use App\Observers\LanguageObserver;

    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            //
        }

        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            Language::observe(LanguageObserver::class);

            LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
                $switch
                    ->locales(Config::get('translatable.locales'));
            });
        }
    }
