<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'push_notification_enabled',
        'sound_enabled',
        'all_notification_disabled',
    ];

    protected $casts = [
        'push_notification_enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'all_notification_disabled' => 'boolean',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
