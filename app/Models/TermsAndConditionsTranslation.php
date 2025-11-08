<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAndConditionsTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'terms_and_conditions_id',
        'locale',
        'title',
        'content',
    ];

    public $timestamps = true;
}
