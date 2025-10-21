<?php

namespace App\Models;

use App\Enums\InputTypeEnum;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Document extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['name'];

    protected $fillable = [
        'input_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'input_type' => InputTypeEnum::class,
    ];

    #[Scope]
    public function searchName(Builder $query, string $search): Builder
    {
        return $query->whereTranslationLike('name', "%{$search}%");
    }

    #[Scope]
    protected function active(Builder $query)
    {
        return $this->where('is_active', true);
    }

    public function customerTypes()
    {
        return $this->belongsToMany(
            CustomerType::class,
            'customer_type_document_rental_shop',
            'document_id',
            'customer_type_id'
        );
    }

    public function rentalShops()
    {
        return $this->belongsToMany(
            RentalShop::class,
            'customer_type_document_rental_shop',
            'document_id',
            'rental_shop_id'
        );
    }
}
