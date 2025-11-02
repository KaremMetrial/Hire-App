<?php

namespace App\Jobs;

use App\Services\OtpService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    protected string $identifier;
    protected string $targetType;
    protected string $purpose;

    /**
     * Create a new job instance.
     *
     * @param string $identifier The identifier for OTP (session token, phone, etc.)
     * @param string $targetType The type of target (user, vendor, etc.)
     * @param string $purpose The purpose of the OTP (pre_registration, password_reset, etc.)
     */
    public function __construct(string $identifier, string $targetType, string $purpose)
    {
        $this->identifier = $identifier;
        $this->targetType = $targetType;
        $this->purpose = $purpose;
    }

    /**
     * Execute the job.
     *
     * @param OtpService $otpService
     * @return void
     */
    public function handle(OtpService $otpService): void
    {
        try {

            // Dispatch notification job asynchronously
            \App\Jobs\SendOtpNotificationJob::dispatch(
                $this->identifier,
                $this->targetType,
                $this->purpose,
                $otp
            );

            Log::info('OTP generated and notification queued', [
                'identifier' => $this->identifier,
                'target_type' => $this->targetType,
                'purpose' => $this->purpose,
                'otp' => config('app.debug') ? $otp : 'hidden',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate OTP via job', [
                'identifier' => $this->identifier,
                'target_type' => $this->targetType,
                'purpose' => $this->purpose,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendOtpJob failed permanently', [
            'identifier' => $this->identifier,
            'target_type' => $this->targetType,
            'purpose' => $this->purpose,
            'error' => $exception->getMessage(),
        ]);
    }
}
