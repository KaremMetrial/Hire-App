<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\UserPreRegistration;
use App\Notifications\SendOtpNotification;
use App\Repositories\Interfaces\OtpRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class OtpService
{
    protected OtpRepositoryInterface $otpRepository;
    protected NotificationService $notificationService;

    public function __construct(OtpRepositoryInterface $otpRepository, NotificationService $notificationService)
    {
        $this->otpRepository = $otpRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Generate and store OTP without sending notification.
     * This method only handles OTP creation and storage.
     *
     * @param string $identifier
     * @param string $type
     * @param string $purpose
     * @return string
     */
    public function generateOtp(string $identifier, string $type, string $purpose = 'pre_registration'): string
    {
        $otp = random_int(10000, 99999); // Generate 5-digit OTP

        $this->otpRepository->createOtp($identifier, $otp, $purpose, $type);

        \App\Jobs\SendOtpNotificationJob::dispatch(
            $identifier,
            $type,
            $purpose,
            $otp
        );

        return $otp;
    }

    /**
     * Send OTP notification to the appropriate entity.
     * This method handles finding the notifiable and sending the notification.
     *
     * @param string $identifier
     * @param string $type
     * @param string $purpose
     * @param string $otp
     * @return void
     */
    public function sendOtpNotification(string $identifier, string $type, string $purpose, string $otp): void
    {
        $notifiable = $this->notificationService->findNotifiable($identifier, $type, $purpose);

        if ($notifiable) {
            Notification::send($notifiable, new SendOtpNotification($otp));
        }
    }

    /**
     * Legacy method for backward compatibility.
     * Generates OTP and sends notification synchronously.
     *
     * @param string $identifier
     * @param string $type
     * @param string $purpose
     * @return string
     */
    public function sendOtp(string $identifier, string $type, string $purpose = 'pre_registration'): string
    {
        $otp = $this->generateOtp($identifier, $type, $purpose);
        $this->sendOtpNotification($identifier, $type, $purpose, $otp);

        return $otp;
    }



    public function verifyOtp(string $identifier, string $otp, string $type, string $purpose = 'pre_registration'): bool
    {
        $existingOtp = $this->otpRepository->findValidOtp($identifier, $purpose, $type);
        if (! $existingOtp) {
            return false;
        }

        if (Hash::check($otp, $existingOtp->otp_hash)) {
            $this->otpRepository->consumeOtp($existingOtp);

            return true;
        }

        return false;
    }
}
