<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserPreRegistration;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class RegistrationService
{
    use ApiResponse, FileUploadTrait;

    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Pre-register user with basic information and documents.
     * Handles file uploads, creates pre-registration record, and sends OTP.
     *
     * @param array $data Validated input data
     * @return array Response data with session token and OTP status
     * @throws \Illuminate\Database\QueryException|\Illuminate\Validation\ValidationException|\Exception
     */
    public function preRegister(array $data): array
    {
        $uploadedFiles = [];

        try {
            // Step 1: Handle file uploads outside transaction for better error isolation
            $uploadedFiles = $this->handleFileUploads($data);

            // Step 2: Prepare data for pre-registration
            $preRegistrationData = array_merge($data, $uploadedFiles, [
                'session_token' => UserPreRegistration::generateSessionToken(),
                'expires_at' => now()->addMinutes(30),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Step 3: Execute database operations in transaction
            DB::beginTransaction();

            $preRegistration = UserPreRegistration::create($preRegistrationData);

            // Step 4: Generate OTP synchronously and send notification asynchronously
            $otpService = app(OtpService::class);

            $otp = $otpService->generateOtp($preRegistration->session_token, 'user', 'pre_registration');

            DB::commit();

            return [
                'session_token' => $preRegistration->session_token,
                'expires_at' => $preRegistration->expires_at,
                'otp_sent' => true,
                'otp' => config('app.debug') ? $otp : null, // Return OTP in debug mode for testing
            ];

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $this->cleanupUploadedFiles($uploadedFiles);
            throw new \Exception(__('message.database_error'), 0, $e);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->cleanupUploadedFiles($uploadedFiles);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupUploadedFiles($uploadedFiles);
            throw $e;
        }
    }

    /**
     * Step 3: Complete registration with password after OTP verification
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function completeRegistration(array $data): User
    {
        // Verify OTP first
        $isVerified = $this->otpService->verifyOtp(
            $data['identifier'],
            $data['otp'],
            'user',
            'pre_registration'
        );
        if (!$isVerified) {
            throw new Exception(__('message.otp.invalid'));
        }

        // Find the pre-registration record
        $preRegistration = UserPreRegistration::findByIdentifier($data['identifier']);
        if (!$preRegistration) {
            throw new Exception(__('message.registration.pre_registration_not_found'));
        }

        if ($preRegistration->isExpired()) {
            throw new Exception(__('message.registration.pre_registration_expired'));
        }

        // Validate session security
        if (!$preRegistration->validateSessionSecurity(request()->ip(), request()->userAgent())) {
            throw new Exception(__('message.registration.session_security_validation_failed'));
        }

        DB::beginTransaction();

        try {
            $hashedPassword = Hash::make($data['password']);

            // Create the actual user
            $user = User::create([
                'name' => $preRegistration->name,
                'country_id' => $preRegistration->country_id,
                'phone' => $preRegistration->phone,
                'email' => $preRegistration->email,
                'birthday' => $preRegistration->birthday,
                'password' => $hashedPassword,
                'face_license_id_photo' => $preRegistration->face_license_id_photo,
                'back_license_id_photo' => $preRegistration->back_license_id_photo,
                'avatar' => $preRegistration->avatar,
            ]);

            // Delete the pre-registration record
            $preRegistration->delete();

            DB::commit();

            return $user;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle file uploads for pre-registration
     *
     * @param array $data
     * @return array
     */
    private function handleFileUploads(array $data): array
    {
        $uploadedFiles = [];

        try {
            // Upload face license photo
            if (isset($data['face_license_id_photo'])) {
                $uploadedFiles['face_license_id_photo'] = $this->upload(
                    request(),
                    'face_license_id_photo',
                    'users/face_license_id_photo'
                );
            }

            // Upload back license photo
            if (isset($data['back_license_id_photo'])) {
                $uploadedFiles['back_license_id_photo'] = $this->upload(
                    request(),
                    'back_license_id_photo',
                    'users/back_license_id_photo'
                );
            }

            // Upload avatar (optional)
            if (isset($data['avatar'])) {
                $uploadedFiles['avatar'] = $this->upload(
                    request(),
                    'avatar',
                    'users/avatars'
                );
            }

            return $uploadedFiles;
        } catch (\Exception $e) {
            // Clean up any files that were uploaded before the error
            $this->cleanupUploadedFiles($uploadedFiles);
            throw $e;
        }
    }

    /**
     * Clean up uploaded files in case of error
     *
     * @param array $files
     * @return void
     */
    private function cleanupUploadedFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists(public_path($file))) {
                unlink(public_path($file));
            }
        }
    }

    /**
     * Resend OTP for pre-registration.
     * Extends the expiration time and dispatches a new OTP job.
     *
     * @param string $sessionToken
     * @return array
     * @throws \Exception
     */
    public function resendOtp(string $sessionToken): array
    {
        $preRegistration = UserPreRegistration::findBySessionToken($sessionToken);

        if ($preRegistration->isExpired()) {
            throw new \Exception(__('message.registration.pre_registration_expired'));
        }
        // Extend expiration time
        $preRegistration->update([
            'expires_at' => now()->addMinutes(30),
        ]);

        $otp = $this->otpService->generateOtp($preRegistration->session_token, 'user', 'pre_registration');


        return [
            'session_token' => $preRegistration->session_token,
            'expires_at' => $preRegistration->expires_at,
            'otp_sent' => true,
            'otp' => config('app.debug') ? $otp : null, // Return OTP in debug mode for testing
        ];
    }
}
