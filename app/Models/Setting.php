<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get cancellation policy with localization support
     */
    public static function getCancellationPolicy(): array
    {
        try {
            return static::get('cancellation_policy', [
                'en' => 'Cancellation Policy: Free cancellation and full refund before 9 hours of booking date.',
                'ar' => 'سياسة الإلغاء: الإلغاء مجاني واسترداد كامل المبلغ قبل 9 ساعات من تاريخ الحجز.',
            ]);
        } catch (\Exception $e) {
            return [
                'en' => 'Cancellation Policy: Free cancellation and full refund before 9 hours of booking date.',
                'ar' => 'سياسة الإلغاء: الإلغاء مجاني واسترداد كامل المبلغ قبل 9 ساعات من تاريخ الحجز.',
            ];
        }
    }

    /**
     * Get cancellation policy text for current locale (based on Accept-Language header)
     */
    public static function getCancellationPolicyText(): string
    {
        $policies = static::getCancellationPolicy();
        $locale = request()->header('Accept-Language') === 'ar' ? 'ar' : 'en';

        return $policies[$locale] ?? $policies['en'];
    }
}
