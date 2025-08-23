<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Governorate extends Model implements TranslatableContract
{
    use Translatable;
    public $translatedAttributes = ['name'];
    protected $fillable = [
        'country_id',
    ];

    /*
     * Scopes Query to search by name
     */
    #[Scope]
    public function searchName(Builder $query, string $search): Builder
    {
        return $query->whereTranslationLike('name', "%{$search}%");
    }

    /*
     * Relationship to Country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    /*
     * Relationship to Cities
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
