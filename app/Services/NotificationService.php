<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\UserPreRegistration;

class NotificationService
{
    /**
     * Find the notifiable entity based on identifier and type.
     *
     * @param string $identifier
     * @param string $type
     * @param string $purpose
     * @return mixed|null
     */
    public function findNotifiable(string $identifier, string $type, string $purpose)
    {
        if ($type === 'user') {
            $notifiable = User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            // For pre-registration, user doesn't exist yet, check UserPreRegistration
            if (!$notifiable && $purpose === 'pre_registration') {
                $notifiable = UserPreRegistration::where('session_token', $identifier)->first();
            }

            return $notifiable;
        } elseif ($type === 'vendor') {
            return Vendor::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();
        }

        return null;
    }
}
