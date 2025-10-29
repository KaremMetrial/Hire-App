<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\User\RentalShopResourece;
use App\Http\Resources\User\UserResourece;
use App\Services\User\AuthService;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse, FileUploadTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
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

        $token = $user->createToken('vendor')->plainTextToken;

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
}
