<?php
namespace App\Repositories\Interfaces;
use App\Models\Otp;

interface OtpRepositoryInterface
{
    public function createOtp(string $identifier, string $otp, string $purpose, string $type): Otp;
    public function findValidOtp(string $identifier, string $purpose, string $type): ?Otp;
    public function consumeOtp(Otp $otp): void;
}
