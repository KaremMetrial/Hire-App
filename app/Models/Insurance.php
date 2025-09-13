<?php

    namespace App\Models;

    use App\Enums\InsurancePeriodEnum;
    use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
    use Astrotomic\Translatable\Translatable;
    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    class Insurance extends Model implements TranslatableContract
    {
        use Translatable;

        public $translatedAttributes = ['title', 'description'];

        protected $fillable = [
            'period',
            'price',
            'deposit_price',
            'is_required',
            'is_active',
        ];
        protected $casts = [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'period' => InsurancePeriodEnum::class,
        ];

        /*
         * Scopes Query to get Only Active Country
         */
        #[Scope]
        protected function active(Builder $query)
        {
            return $this->where('is_active', true);
        }

        /*
         * Scopes Query to search by title
         */
        #[Scope]
        public function searchTitle(Builder $query, string $search): Builder
        {
            return $query->whereTranslationLike('title', "%{$search}%");
        }

        /*
         * Relations
         */
        public function cars(): BelongsToMany
        {
            return $this->belongsToMany(Car::class);
        }
    }
