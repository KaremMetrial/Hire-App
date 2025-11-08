<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CompleteRegistrationRequest;
use App\Http\Requests\Vendor\LoginRequest;
use App\Http\Requests\Vendor\PreRegisterRequest;
use App\Http\Requests\Vendor\RegisterRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\Vendor\RentalShopResourece;
use App\Http\Resources\Vendor\VendorResourece;
use App\Models\VendorPreRegistration;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use App\Services\OtpService;
use App\Services\Vendor\AuthService;
use App\Services\Vendor\RegistrationService;
use App\Services\Vendor\RentalShopService;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse, FileUploadTrait;

    protected AuthService $authService;

    protected RentalShopService $rentalShopService;

    protected RegistrationService $registrationService;

    protected VendorRepositoryInterface $vendorRepository;

    protected OtpService $otpService;

    public function __construct(AuthService $authService, RentalShopService $rentalShopService, RegistrationService $registrationService, VendorRepositoryInterface $vendorRepository, OtpService $otpService)
    {
        $this->authService = $authService;
        $this->rentalShopService = $rentalShopService;
        $this->registrationService = $registrationService;
        $this->vendorRepository = $vendorRepository;
        $this->otpService = $otpService;
    }

    /**
     * Pre-register vendor with basic information and documents
     */
    public function preRegister(PreRegisterRequest $request)
    {
        try {
            $result = $this->registrationService->preRegister($request->validated());

            return $this->successResponse($result, __('message.auth.pre_register_success'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Complete registration with password after OTP verification
     */
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        try {
            $result = $this->registrationService->completeRegistration($request->validated());

            $token = $result['vendor']->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'vendor' => new VendorResourece($result['vendor']),
                'rental_shop' => new RentalShopResourece($result['rental_shop']),
                'token' => $token,
            ], __('message.auth.register'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Resend OTP for pre-registration
     */
    public function resendPreRegisterOtp(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string|exists:vendor_pre_registrations,session_token',
        ]);

        try {
            $result = $this->registrationService->resendOtp($request->session_token);

            return $this->successResponse($result, __('message.otp.sent'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['vendor']['national_id_photo'] = $this->upload($request, 'vendor.national_id_photo', 'vendors/national_id_photos');
        $validated['rental_shop']['image'] = $this->upload($request, 'rental_shop.image', 'vendors/rental_shops');
        $validated['rental_shop']['transport_license_photo'] = $this->upload($request, 'rental_shop.transport_license_photo', 'vendors/transport_license_photos');
        $validated['rental_shop']['commerical_registration_photo'] = $this->upload($request, 'rental_shop.commerical_registration_photo', 'vendors/commerical_registration_photos');

        DB::beginTransaction();
        try {
            $vendor = $this->authService->register($validated['vendor']);
            $rentalShop = $this->rentalShopService->create($validated['rental_shop']);

            $vendor->rentalShops()->attach($rentalShop->id, ['role' => 'manager']);

            $vendor->refresh();
            $rentalShop->refresh();
            $token = $vendor->createToken('auth_token')->plainTextToken;

            DB::commit();

            return $this->successResponse([
                'vendor' => new VendorResourece($vendor),
                'rental_shop' => new RentalShopResourece($rentalShop),
                'token' => $token,
            ], __('message.auth.register'));
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(__('message.unexpected_error').$e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $vendor = $this->authService->login($validated);

        if (! $vendor) {
            return $this->errorResponse(__('message.auth.login.invalid_credentials'));
        }

        $token = $vendor->createToken('vendor')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'vendor' => new VendorResourece($vendor),
            'rental_shop' => new RentalShopResourece($vendor->rentalShops()->first()),
        ], __('message.auth.login'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, __('message.auth.logout'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
        ]);

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $vendor = $this->vendorRepository->findBy($field, $identifier);

        if (!$vendor) {
            return $this->errorResponse(__('message.auth.user_not_found'), 404);
        }

        // Send OTP for password reset
        $otpService = app(OtpService::class);
        $otp = $otpService->generateOtp($identifier, 'vendor', 'forgot_password');

        return $this->successResponse([
            'otp' => config('app.debug') ? $otp : null,
        ], __('message.otp.sent'));
    }

    public function resendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
        ]);

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $vendor = $this->vendorRepository->findBy($field, $identifier);

        if (!$vendor) {
            return $this->errorResponse(__('message.auth.user_not_found'), 404);
        }

        // Resend OTP for password reset
        $otpService = app(OtpService::class);
        $otp = $otpService->generateOtp($identifier, 'vendor', 'forgot_password');

        return $this->successResponse([
            'otp' => config('app.debug') ? $otp : null,
        ], __('message.otp.sent'));
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'otp' => 'required',
        ]);

        $otpService = app(OtpService::class);
        $verified = $otpService->verifyOtp($request->identifier, $request->otp, 'vendor', 'forgot_password');

        if (!$verified) {
            return $this->errorResponse(__('message.otp.invalid'), 422);
        }

        return $this->successResponse(null, __('message.otp.verified'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'otp' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $otpService = app(OtpService::class);
        $verified = $otpService->verifyOtp($request->identifier, $request->otp, 'vendor', 'forgot_password');

        if (!$verified) {
            return $this->errorResponse(__('message.otp.invalid'), 422);
        }

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $vendor = $this->vendorRepository->findBy($field, $identifier);

        if (!$vendor) {
            return $this->errorResponse(__('message.auth.user_not_found'), 404);
        }

        $this->authService->resetPassword($vendor, $request->password);

        return $this->successResponse(null, __('message.password_reset'));
    }

    public function me(Request $request)
    {
        $vendor = $request->user();

        return $this->successResponse([
            'vendor' => new VendorResourece($vendor),
        ], __('message.success'));
    }

    public function updateProfile(UpdateVendorRequest $request)
    {
        $vendor = Auth::user();
        $validated = $request->validated();

        if (isset($validated['otp_code'])) {
            $isOtpVerified = $this->otpService->verifyOtp(
                $vendor->phone,
                $validated['otp_code'],
                'vendor',
                'update-vendor-info'
            );
            if (!$isOtpVerified) {
                return $this->errorResponse(__('message.otp.invalid'));
            }

            unset($validated['otp_code']);
            $validated = array_filter($validated, fn ($value) => $value !== null);
            $vendor->update($validated);

            return $this->successResponse([
                'vendor' => new VendorResourece($vendor),
            ], __('message.success'));
        }

        $this->otpService->sendOtp($vendor->phone, 'vendor', 'update-vendor-info');

        return $this->successResponse(null, __('message.otp.sent'));
    }
}
