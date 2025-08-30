<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use App\Services\OtpService;
use Illuminate\Database\Eloquent\Model;

class ValidOtpRule implements ValidationRule
{
    protected OtpService $otpService;
    protected string $purpose;
    protected string $type;
    protected string $errorMessage = 'message.otp.invalid';

    /**
     * @param  OtpService  $otpService
     * @param  string  $type  // 'user' أو 'vendor'
     * @param  string  $purpose
     */
    public function __construct(OtpService $otpService, string $type, string $purpose = 'pre_registration')
    {
        $this->otpService = $otpService;
        $this->purpose = $purpose;
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string, ?string=): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $identifier = $value['identifier'] ?? null;
        $otp = $value['otp'] ?? null;

        if (! $identifier || ! $otp || ! $this->otpService->verifyOtp($identifier, $otp, $this->type, $this->purpose)) {
            $fail(__($this->errorMessage));
        }
    }
}
