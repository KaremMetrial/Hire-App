<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Hash;

    class Otp extends Model
    {
        protected $fillable = [
            'identifier',
            'otp_hash',
            'purpose',
            'type',
            'expires_at',
            'consumed_at',
        ];

        protected $casts = [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];

        /*
         * Check if the OTP is expired
         */
        public function isExpired(): bool
        {
            return now()->greaterThan($this->expires_at);
        }

        /*
         * Verify the OTP
         */
        public function verify(string $otp): bool
        {
            if ($this->isExpired() || $this->consumed_at) {
                return false;
            }

            return Hash::check($otp, $this->otp_hash);
        }

        /*
         * Consume the OTP
         */
        public function consume(): void
        {
            $this->consumed_at = now();
            $this->save();
        }
    }
