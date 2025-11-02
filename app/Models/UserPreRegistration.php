<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserPreRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'phone',
        'email',
        'birthday',
        'face_license_id_photo',
        'back_license_id_photo',
        'avatar',
        'session_token',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'birthday' => 'date',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a unique session token
     */
    public static function generateSessionToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('session_token', $token)->exists());

        return $token;
    }

    /**
     * Check if the pre-registration is expired
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Scope for non-expired records
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Find by session token
     */
    public static function findBySessionToken(string $token): ?self
    {
        return self::valid()->where('session_token', $token)->first();
    }

    /**
     * Find by identifier (phone or email)
     */
    public static function findByIdentifier(string $identifier): ?self
    {
        return self::valid()
            ->where(function ($query) use ($identifier) {
                $query->where('phone', $identifier)
                      ->orWhere('email', $identifier)
                      ->orWhere('session_token', $identifier);
            })
            ->first();
    }

    /**
     * Validate session security (IP and user agent)
     */
    public function validateSessionSecurity(string $ipAddress, ?string $userAgent = null): bool
    {
        // Check IP address
        if ($this->ip_address !== $ipAddress) {
            return false;
        }

        // Check user agent if provided and stored
        if ($userAgent && $this->user_agent && $this->user_agent !== $userAgent) {
            return false;
        }

        return true;
    }

    /**
     * Relationship with Country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Clean up expired records (can be called by a scheduled job)
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<=', now())->delete();
    }

    /**
     * Route notifications for the given channel.
     *
     * @param  string  $channel
     * @return array|string|null
     */
    public function routeNotificationFor($channel)
    {
        if ($channel === 'mail') {
            return $this->email;
        }

        if ($channel === 'sms') {
            return $this->phone;
        }

        return null;
    }
}
