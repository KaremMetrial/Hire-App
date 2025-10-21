<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['name'];

    protected $fillable = [
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    * Scopes Query to search by name
    */
    #[Scope]
    public function searchName(Builder $query, string $search): Builder
    {
        return $query->whereTranslationLike('name', "%{$search}%");
    }

    public function documents()
    {
        return $this->belongsToMany(
            Document::class,
            'customer_type_document_rental_shop',
            'customer_type_id',
            'document_id'
        );
    }

    public function rentalShops()
    {
        return $this->belongsToMany(
            RentalShop::class,
            'customer_type_document_rental_shop',
            'customer_type_id',
            'rental_shop_id'
        );
    }
}
