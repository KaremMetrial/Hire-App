<?php
    namespace App\Repositories;

    use App\Models\Otp;
    use Illuminate\Support\Facades\Hash;
    use App\Repositories\Interfaces\OtpRepositoryInterface;

    class OtpRepository implements OtpRepositoryInterface
    {
        public function createOtp(string $identifier, string $otp, string $purpose, string $type): Otp
        {
            return Otp::create([
                'identifier' => $identifier,
                'otp_hash' => Hash::make($otp),
                'purpose' => $purpose,
                'type' => $type,
                'expires_at' => now()->addMinutes(10),
            ]);
        }

        public function findValidOtp(string $identifier, string $purpose, string $type): ?Otp
        {
            return Otp::where('identifier', $identifier)
                ->where('purpose', $purpose)
                ->where('type', $type)
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();
        }

        public function consumeOtp(Otp $otp): void
        {
            $otp->consume();
        }
    }
