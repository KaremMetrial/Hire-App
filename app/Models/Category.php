<?php

    namespace App\Models;

    use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
    use Astrotomic\Translatable\Translatable;
    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;

    class Category extends Model implements TranslatableContract
    {
        use Translatable;

        public $translatedAttributes = ['name'];

        protected $fillable = [
            'icon',
        ];

        #[Scope]
        public function searchName(Builder $query, string $search): Builder
        {
            return $query->whereTranslationLike('name', "%{$search}%");
        }
    }

