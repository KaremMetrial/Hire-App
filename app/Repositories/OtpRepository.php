<?php
    namespace App\Repositories;

    use App\Models\Otp;
    use Illuminate\Support\Carbon;

    class OtpRepository
    {
        public function create($userId, $otpHash, $expiresAt)
        {
            return Otp::create([
                'user_id' => $userId,
                'otp_hash' => $otpHash,
                'expires_at' => $expiresAt,
                'is_used' => false,
            ]);
        }

        public function latestValidOtp($userId)
        {
            return Otp::where('user_id', $userId)
                ->where('is_used', false)
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();
        }

        public function markUsed(Otp $otp)
        {
            $otp->update(['is_used' => true]);
        }
    }
