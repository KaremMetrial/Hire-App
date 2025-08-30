<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Foundation\Auth\User as Authenticatable;

    class Admin extends Authenticatable
    {
        use HasFactory;

        protected $fillable = [
            'name',
            'email',
            'password',
            'phone',
        ];
        protected $hidden = [
            'password',
        ];
        protected $casts = [
            'password' => 'hashed',
        ];

        /*
         * Relationships to Vendor
         */
        public function vendors(): HasMany
        {
            return $this->hasMany(Vendor::class);
        }
    }
