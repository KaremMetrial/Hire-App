<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CompleteRegistrationRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\PreRegisterRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\User\RentalShopResourece;
use App\Http\Resources\User\UserResourece;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\OtpService;
use App\Services\User\AuthService;
use App\Services\User\RegistrationService;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse, FileUploadTrait;

    protected AuthService $authService;
    protected RegistrationService $registrationService;
    protected UserRepositoryInterface $userRepository;

    public function __construct(AuthService $authService, RegistrationService $registrationService, UserRepositoryInterface $userRepository)
    {
        $this->authService = $authService;
        $this->registrationService = $registrationService;
        $this->userRepository = $userRepository;
    }

    /**
     * Step 1: Pre-register user with basic information and documents
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
     * Step 3: Complete registration with password after OTP verification
     */
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        try {
            $user = $this->registrationService->completeRegistration($request->validated());

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => new UserResourece($user),
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
            'session_token' => 'required|string|exists:user_pre_registrations,session_token',
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
        if ($request->hasFile('user.face_license_id_photo')) {
            $validated['user']['face_license_id_photo'] = $this->upload(
                $request,
                'user.face_license_id_photo',
                'users/face_license_id_photo'
            );
        }
        if ($request->hasFile('user.back_license_id_photo')) {
            $validated['user']['back_license_id_photo'] = $this->upload(
                $request,
                'user.back_license_id_photo',
                'users/back_license_id_photo'
            );
        }
        if ($request->hasFile('user.avatar')) {
            $validated['user']['avatar'] = $this->upload(
                $request,
                'user.avatar',
                'users/avatars'
            );
        }

        DB::beginTransaction();
        try {
            $user = $this->authService->register($validated['user']);

            $user->refresh();
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return $this->successResponse([
                'user' => new UserResourece($user),
                'token' => $token,
            ], __('message.auth.register'));
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(__('message.unexpected_error') . $e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $user = $this->authService->login($validated);
        if (!$user) {
            return $this->errorResponse(__('message.auth.login.invalid_credentials'));
        }

        $token = $user->createToken('auth_token')->plainTextToken; // Standardized token name for consistency

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResourece($user),
        ], __('message.auth.login'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, __('message.auth.logout'));
    }

    public function me(Request $request)
    {
        return $this->successResponse(
            ['user' => new UserResourece($request->user())],
            __('message.success'));
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        DB::beginTransaction();
        try {
            // Revoke all tokens
            $user->tokens()->delete();

            // Soft delete the user
            $user->delete();

            DB::commit();

            return $this->successResponse(null, __('message.success'));
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(__('message.unexpected_error') . $e->getMessage());
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $files = ['face_license_id_photo', 'back_license_id_photo', 'avatar'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $validated[$file] = $this->upload(
                    $request,
                    $file,
                    'users/' . str_replace('_', '/', $file)
                );
            }
        }

        // Handle terms acceptance if provided
        if ($request->has('accept_terms_version')) {
            $termsVersion = $request->accept_terms_version;
            $terms = \App\Models\TermsAndConditions::getByVersion($termsVersion);

            if ($terms && !$user->acceptedTerms()->where('terms_and_conditions_id', $terms->id)->exists()) {
                $user->acceptedTerms()->attach($terms->id, [
                    'accepted_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        try {
            $user = $this->authService->updateProfile($user, $validated);
            $user->refresh();
            return $this->successResponse([
                'user' => new UserResourece($user),
            ], __('message.success'));
        } catch (\Exception $e) {
            return $this->errorResponse(__('message.unexpected_error') . $e->getMessage());
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
        ]);

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = $this->userRepository->findBy($field, $identifier);

        if (!$user) {
            return $this->errorResponse(__('message.auth.user_not_found'), 404);
        }

        // Send OTP for password reset
        $otpService = app(OtpService::class);
        $otpService->generateOtp($identifier, 'user', 'forgot_password');

        return $this->successResponse(null, __('message.otp.sent'));
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'otp' => 'required',
        ]);

        $otpService = app(OtpService::class);
        $verified = $otpService->verifyOtp($request->identifier, $request->otp, 'user', 'forgot_password');

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
        $verified = $otpService->verifyOtp($request->identifier, $request->otp, 'user', 'forgot_password');

        if (!$verified) {
            return $this->errorResponse(__('message.otp.invalid'), 422);
        }

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = $this->userRepository->findBy($field, $identifier);

        if (!$user) {
            return $this->errorResponse(__('message.auth.user_not_found'), 404);
        }

        $this->authService->resetPassword($user, $request->password);

        return $this->successResponse(null, __('message.password_reset'));
    }
}
