<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsAndConditions extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['title', 'content'];

    protected $fillable = [
        'version',
        'is_active',
        'is_required_agreement',
        'effective_date',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_required_agreement' => 'boolean',
        'effective_date' => 'datetime',
    ];

    #[Scope]
    public static function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    public static function latestVersion(Builder $query): Builder
    {
        return $query->orderBy('version', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the current active terms and conditions
     */
    public static function getCurrent(): ?self
    {
        return static::query()->active()
            ->where(function ($query) {
                $query->whereNull('effective_date')
                    ->orWhere('effective_date', '<=', now());
            })
            ->latestVersion()
            ->first();
    }

    /**
     * Get terms and conditions by version
     */
    public static function getByVersion(string $version): ?self
    {
        return static::where('version', $version)->first();
    }

    /**
     * Activate this version (deactivate others)
     */
    public function activate(): bool
    {
        // Deactivate all other versions
        static::where('is_active', true)->update(['is_active' => false]);

        // Activate this version
        return $this->update(['is_active' => true]);
    }
}
