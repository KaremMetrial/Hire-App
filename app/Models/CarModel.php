<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarModel extends Model implements TranslatableContract
{
    use HasFactory, Translatable;
    protected $table = 'models';
    protected $translationForeignKey = 'model_id';
    public $translatedAttributes = ['name'];
    protected $fillable = [
       'is_active',
        'brand_id',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    #[Scope]
    public function searchName(Builder $query, string $search): Builder
    {
        return $query->whereTranslationLike('name', "%{$search}%");
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
