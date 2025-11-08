<?php

namespace App\Services\Vendor;

use App\Models\Vendor;
use App\Models\VendorPreRegistration;
use App\Models\RentalShop;
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
     * Pre-register vendor with basic information and documents.
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
                'session_token' => VendorPreRegistration::generateSessionToken(),
                'expires_at' => now()->addMinutes(30),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Step 3: Execute database operations in transaction
            DB::beginTransaction();

            $preRegistration = VendorPreRegistration::create($preRegistrationData);

            // Step 4: Generate OTP synchronously and send notification asynchronously
            $otpService = app(OtpService::class);

            $otp = $otpService->generateOtp($preRegistration->session_token, 'vendor', 'pre_registration');

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
        } catch (\Exception $e) {
            // Clean up any files that were uploaded before the error
            $this->cleanupUploadedFiles($uploadedFiles);
            throw $e;
        }
    }

    /**
     * Handle file uploads for vendor pre-registration
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function handleFileUploads(array $data): array
    {
        $uploadedFiles = [];

        try {
            // Upload national ID photo
            if (isset($data['national_id_photo'])) {
                $uploadedFiles['national_id_photo'] = $this->upload(
                    request(),
                    'national_id_photo',
                    'vendors/national_id_photos'
                );
            }

            // Upload rental shop image
            if (isset($data['rental_shop_image'])) {
                $uploadedFiles['rental_shop_image'] = $this->upload(
                    request(),
                    'rental_shop_image',
                    'vendors/rental_shops'
                );
            }

            // Upload transport license photo
            if (isset($data['transport_license_photo'])) {
                $uploadedFiles['transport_license_photo'] = $this->upload(
                    request(),
                    'transport_license_photo',
                    'vendors/transport_license_photos'
                );
            }

            // Upload commercial registration photo
            if (isset($data['commerical_registration_photo'])) {
                $uploadedFiles['commerical_registration_photo'] = $this->upload(
                    request(),
                    'commerical_registration_photo',
                    'vendors/commerical_registration_photos'
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
     * Complete registration with password after OTP verification
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function completeRegistration(array $data): array
    {
        // Verify OTP first
        $isVerified = $this->otpService->verifyOtp(
            $data['identifier'],
            $data['otp'],
            'vendor',
            'pre_registration'
        );
        if (!$isVerified) {
            throw new Exception(__('message.otp.invalid'));
        }

        // Find the pre-registration record
        $preRegistration = VendorPreRegistration::findByIdentifier($data['identifier']);
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

            // Create the actual vendor
            $vendor = Vendor::create([
                'name' => $preRegistration->name,
                'phone' => $preRegistration->phone,
                'email' => $preRegistration->email,
                'password' => $hashedPassword,
                'national_id_photo' => $preRegistration->national_id_photo,
                'status' => 'pending',
            ]);

            $rentalShop = RentalShop::create([
                'name' => $preRegistration->rental_shop_name,
                'phone' => $preRegistration->rental_shop_phone,
                'image' => $preRegistration->rental_shop_image,
                'transport_license_photo' => $preRegistration->transport_license_photo,
                'commerical_registration_photo' => $preRegistration->commerical_registration_photo,
                'address' => $preRegistration->rental_shop_address,
                'status' => 'pending',
            ]);

            $vendor->rentalShops()->attach($rentalShop->id, ['role' => 'manager']);

            // Delete the pre-registration record
            $preRegistration->delete();

            DB::commit();

            return [
                'vendor' => $vendor,
                'rental_shop' => $rentalShop,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
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
        $preRegistration = VendorPreRegistration::findBySessionToken($sessionToken);

        if ($preRegistration->isExpired()) {
            throw new \Exception(__('message.registration.pre_registration_expired'));
        }
        // Extend expiration time
        $preRegistration->update([
            'expires_at' => now()->addMinutes(30),
        ]);

        $otp = $this->otpService->generateOtp($preRegistration->session_token, 'vendor', 'pre_registration');

        return [
            'session_token' => $preRegistration->session_token,
            'expires_at' => $preRegistration->expires_at,
            'otp_sent' => true,
            'otp' => config('app.debug') ? $otp : null, // Return OTP in debug mode for testing
        ];
    }
}
