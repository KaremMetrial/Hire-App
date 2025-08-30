<?php

    namespace App\Http\Controllers\Api;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\SendOtpRequest;
    use App\Http\Requests\VerifyOtpRequest;
    use App\Services\OtpService;
    use App\Traits\ApiResponse;

    class OtpController extends Controller
    {
        use ApiResponse;

        protected OtpService $otpService;

        public function __construct(OtpService $otpService)
        {
            $this->otpService = $otpService;
        }

        public function sendOtp(SendOtpRequest $request)
        {
            $validated = $request->validated();

            return $this->successResponse([
                'otp' => $this->otpService->sendOtp($validated['identifier'], $validated['type']),
            ], __('message.otp.sent'));
        }

        public function verifyOtp(VerifyOtpRequest $request)
        {
            $validated = $request->validated();
            $validated['purpose'] = $validated['purpose'] ?? 'pre_registration';

            $verified = $this->otpService->verifyOtp(
                $validated['identifier'],
                $validated['otp'],
                $validated['type'],
                $validated['purpose']
            );

            if (!$verified) {
                return $this->errorResponse(__('message.otp.invalid'), 422);
            }

            return $this->successResponse(null, __('message.otp.verified'));
        }

    }
