<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Vendor;
use App\Models\UserPreRegistration;
use App\Notifications\SendOtpNotification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendOtpNotificationJob implements ShouldQueue
{
    use QueueableTrait;

    protected string $identifier;
    protected string $targetType;
    protected string $purpose;
    protected string $otp;
    protected NotificationService $notificationService;

    /**
     * Create a new job instance.
     *
     * @param string $identifier The identifier for OTP (session token, phone, email, etc.)
     * @param string $targetType The type of target (user, vendor, etc.)
     * @param string $purpose The purpose of the OTP (pre_registration, password_reset, etc.)
     * @param string $otp The OTP to send
     */
    public function __construct(string $identifier, string $targetType, string $purpose, string $otp)
    {
        $this->identifier = $identifier;
        $this->targetType = $targetType;
        $this->purpose = $purpose;
        $this->otp = $otp;
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $notifiable = $this->notificationService->findNotifiable($this->identifier, $this->targetType, $this->purpose);

            if ($notifiable) {
                Notification::send($notifiable, new SendOtpNotification($this->otp));

                Log::info('OTP notification sent via job', [
                    'identifier' => $this->identifier,
                    'target_type' => $this->targetType,
                    'purpose' => $this->purpose,
                ]);
            } else {
                Log::warning('No notifiable found for OTP notification', [
                    'identifier' => $this->identifier,
                    'target_type' => $this->targetType,
                    'purpose' => $this->purpose,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send OTP notification via job', [
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
        Log::error('SendOtpNotificationJob failed permanently', [
            'identifier' => $this->identifier,
            'target_type' => $this->targetType,
            'purpose' => $this->purpose,
            'error' => $exception->getMessage(),
        ]);
    }
}
