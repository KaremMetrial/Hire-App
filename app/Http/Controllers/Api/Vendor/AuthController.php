<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\LoginRequest;
use App\Http\Requests\Vendor\RegisterRequest;
use App\Http\Resources\Vendor\RentalShopResourece;
use App\Http\Resources\Vendor\VendorResourece;
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

    public function __construct(AuthService $authService, RentalShopService $rentalShopService)
    {
        $this->authService = $authService;
        $this->rentalShopService = $rentalShopService;
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
}
