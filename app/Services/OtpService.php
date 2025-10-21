<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\SendOtpNotification;
use App\Repositories\Interfaces\OtpRepositoryInterface;
use Illuminate\Support\Facades\Notification;

class OtpService
{
    protected OtpRepositoryInterface $otpRepository;

    public function __construct(OtpRepositoryInterface $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    public function sendOtp(string $identifier, string $type, string $purpose = 'pre_registration'): string
    {
        $otp = mt_rand(10000, 99999);

        $this->otpRepository->createOtp($identifier, $otp, $purpose, $type);

        if ($type === 'user') {
            $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();
        } else {
            $user = Vendor::where('email', $identifier)->orWhere('phone', $identifier)->first();
        }

        if ($user) {
            //                Notification::send($user, new SendOtpNotification($otp));
        }

        return $otp;
    }

    public function verifyOtp(string $identifier, string $otp, string $type, string $purpose = 'pre_registration'): bool
    {
        $existingOtp = $this->otpRepository->findValidOtp($identifier, $purpose, $type);
        if (! $existingOtp) {
            return false;
        }

        if (password_verify($otp, $existingOtp->otp_hash)) {
            $this->otpRepository->consumeOtp($existingOtp);

            return true;
        }

        return false;
    }
}
