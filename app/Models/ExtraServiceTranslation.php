<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraServiceTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'description',
    ];
}
