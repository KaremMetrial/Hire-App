<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\Vendor\VendorResourece;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    use ApiResponse;

    public function __construct(protected OtpService $otpService) {}

    public function me(Request $request)
    {
        $vendor = $request->user();

        return $this->successResponse([
            'vendor' => new VendorResourece($vendor),
        ], __('message.success'));
    }

    public function update(UpdateVendorRequest $request)
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
            if (! $isOtpVerified) {
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
