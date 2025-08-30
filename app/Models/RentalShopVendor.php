<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RentalShopVendor extends Model
{
    protected $table = 'rental_shop_vendor';

    protected $fillable = [
        'vendor_id',
        'rental_shop_id',
        'role',
    ];
}
