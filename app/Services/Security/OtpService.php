<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate a numeric OTP for an identifier (e.g., email or phone).
     *
     * @param string $identifier
     * @param int $digits
     * @return string
     */
    public function generate(string $identifier, int $digits = 6): string
    {
        // 1. Check for Developer Bypass
        if (config('communication.otp.bypass_enabled')) {
            return config('communication.otp.bypass_value', '123456');
        }

        // 2. Generate Random
        $min = pow(10, $digits - 1);
        $max = pow(10, $digits) - 1;
        $otp = (string) mt_rand($min, $max);

        // 3. Store in Cache (TTL from config)
        $minutes = config('communication.otp.expiry_minutes', 10);
        Cache::put($this->key($identifier), $otp, now()->addMinutes($minutes));

        return $otp;
    }

    /**
     * Validate an OTP.
     *
     * @param string $identifier
     * @param string $otp
     * @return bool
     */
    public function validate(string $identifier, string $otp): bool
    {
        // 1. Check for Developer Bypass
        if (config('communication.otp.bypass_enabled')) {
            return $otp === config('communication.otp.bypass_value', '123456');
        }

        // 2. Retrieve from Cache
        $cachedOtp = Cache::get($this->key($identifier));

        if (!$cachedOtp) {
            return false;
        }

        // 3. Verify
        if ($cachedOtp === $otp) {
            Cache::forget($this->key($identifier)); // Invalidate after use
            return true;
        }

        return false;
    }

    /**
     * Check an OTP without invalidating it.
     * 
     * @param string $identifier
     * @param string $otp
     * @return bool
     */
    public function check(string $identifier, string $otp): bool
    {
         // 1. Check for Developer Bypass
        if (config('communication.otp.bypass_enabled')) {
            return $otp === config('communication.otp.bypass_value', '123456');
        }

        // 2. Retrieve from Cache
        $cachedOtp = Cache::get($this->key($identifier));

        return $cachedOtp && $cachedOtp === $otp;
    }

    protected function key(string $identifier): string
    {
        return 'otp_verification_' . md5($identifier);
    }
}
