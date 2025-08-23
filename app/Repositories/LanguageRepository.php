<?php


    namespace App\Repositories;

    use App\Models\Language;
    use Illuminate\Support\Facades\Cache;

    class LanguageRepository
    {
        protected $cacheKey = 'available_languages';

        public function getAllCodes(): array
        {
            return Cache::rememberForever($this->cacheKey, function () {
                return Language::active()->pluck('code')->toArray();
            });
        }

        public function clearCache(): void
        {
            Cache::forget($this->cacheKey);
        }
    }
