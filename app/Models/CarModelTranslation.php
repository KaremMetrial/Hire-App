<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModelTranslation extends Model
{
    protected $table = 'model_translations';
    public $timestamps = false;
    protected $fillable = [
        'name',
    ];
}
