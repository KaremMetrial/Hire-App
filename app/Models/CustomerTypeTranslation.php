<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTypeTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
