<?php

    namespace App\Models;

    use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
    use Astrotomic\Translatable\Translatable;
    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;

    class ExtraService extends Model implements TranslatableContract
    {
        use Translatable;

        public $translatedAttributes = ['name','description'];
        protected $fillable = [
            'is_active',
        ];
        protected $casts = [
            'is_active' => 'boolean',
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
         * Scopes Query to search by name
         */
        #[Scope]
        public function searchName(Builder $query, string $search): Builder
        {
            return $query->whereTranslationLike('name', "%{$search}%");
        }

        /*
     * Scopes Query to search by description
     */
        #[Scope]
        public function searchDescription(Builder $query, string $search): Builder
        {
            return $query->whereTranslationLike('description', "%{$search}%");
        }
    }
