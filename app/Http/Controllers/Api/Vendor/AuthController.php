<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\LoginRequest;
use App\Http\Requests\Vendor\RegisterRequest;
use App\Http\Resources\Vendor\RentalShopResourece;
use App\Http\Resources\Vendor\VendorResourece;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use App\Services\OtpService;
use App\Services\Vendor\AuthService;
use App\Services\Vendor\RentalShopService;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse, FileUploadTrait;

    protected AuthService $authService;

    protected RentalShopService $rentalShopService;

    protected VendorRepositoryInterface $vendorRepository;

    public function __construct(AuthService $authService, RentalShopService $rentalShopService, VendorRepositoryInterface $vendorRepository)
    {
        $this->authService = $authService;
        $this->rentalShopService = $rentalShopService;
        $this->vendorRepository = $vendorRepository;
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
        $otpService->generateOtp($identifier, 'vendor', 'forgot_password');

        return $this->successResponse(null, __('message.otp.sent'));
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
}
