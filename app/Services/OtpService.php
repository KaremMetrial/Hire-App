<?php
    namespace App\Services;

    use App\Repositories\OtpRepository;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Notification;
    use App\Notifications\SendOtpNotification;

    class OtpService
    {
        public function __construct(private OtpRepository $otpRepository) {}

        public function generateAndSend($user, $channel = ['mail', 'sms'])
        {
            $otp = rand(100000, 999999);
            $otpHash = Hash::make($otp);
            $expiresAt = Carbon::now()->addMinutes(10);

            $this->otpRepository->create($user->id, $otpHash, $expiresAt);

            Notification::send($user, new SendOtpNotification($otp, $channel));
        }

        public function verify($user, $otpInput)
        {
            $otpRecord = $this->otpRepository->latestValidOtp($user->id);

            if (!$otpRecord) {
                return false;
            }

            if (!Hash::check($otpInput, $otpRecord->otp_hash)) {
                return false;
            }

            $this->otpRepository->markUsed($otpRecord);
            return true;
        }
    }
