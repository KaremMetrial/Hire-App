<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;

    class Language extends Model
    {
        protected $fillable = [
            'name',
            'code',
            'native_name',
            'direction',
            'is_default',
            'is_active',
        ];
        protected $casts = [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];

        /*
         * Scopes Query to get Only Active Language
         */
        #[Scope]
        protected function active(Builder $query)
        {
            return $this->where('is_active', true);
        }
    }
