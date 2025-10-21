<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
