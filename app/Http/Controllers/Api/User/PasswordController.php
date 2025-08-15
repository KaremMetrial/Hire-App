<?php

    namespace App\Http\Controllers\Api\User;

    use App\Http\Controllers\Controller;
    use App\Models\User;
    use App\Services\OtpService;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;

    class PasswordController extends Controller
    {
        public function __construct(private OtpService $otpService)
        {
        }

        public function sendOtp(Request $request)
        {
            $request->validate(['contact' => 'required']);

            $email = filter_var($request->contact, FILTER_VALIDATE_EMAIL) ? $request->contact : null;
            $phone = !$email ? $request->contact : null;

            $user = User::when($email, fn($q) => $q->where('email', $email))
                ->when($phone, fn($q) => $q->where('phone', $phone))
                ->firstOrFail();

            $channels = [];
            if ($email) {
                $channels[] = 'mail';
            }
            if ($phone) {
                $channels[] = 'sms';
            }

            $this->otpService->generateAndSend($user, $channels);

            return response()->json(['message' => 'OTP sent successfully']);
        }

        public function verifyOtp(Request $request)
        {
            $request->validate([
                'contact' => 'required',
                'otp' => 'required|digits:6'
            ]);

            $email = filter_var($request->contact, FILTER_VALIDATE_EMAIL) ? $request->contact : null;
            $phone = !$email ? $request->contact : null;

            $user = User::when($email, fn($q) => $q->where('email', $email))
                ->when($phone, fn($q) => $q->where('phone', $phone))
                ->firstOrFail();

            if (!$this->otpService->verify($user, $request->otp)) {
                return response()->json(['error' => 'Invalid or expired OTP'], 400);
            }

            return response()->json(['message' => 'OTP verified successfully']);
        }

        public function resetPassword(Request $request)
        {
            $request->validate([
                'contact' => 'required',
                'otp' => 'required|digits:6',
                'password' => 'required|string|min:8|confirmed'
            ]);

            $email = filter_var($request->contact, FILTER_VALIDATE_EMAIL) ? $request->contact : null;
            $phone = !$email ? $request->contact : null;

            $user = User::when($email, fn($q) => $q->where('email', $email))
                ->when($phone, fn($q) => $q->where('phone', $phone))
                ->firstOrFail();

            if (!$this->otpService->verify($user, $request->otp)) {
                return response()->json(['error' => 'Invalid or expired OTP'], 400);
            }

            $user->update(['password' => Hash::make($request->password)]);

            return response()->json(['message' => 'Password reset successful']);
        }
    }
