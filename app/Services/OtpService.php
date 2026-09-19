<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\OtpCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    private const OTP_TTL_MINUTES = 10;

    public function generateFor(User $user): string
    {
        $otp = (string) random_int(100000, 999999);

        // Only a hash is stored, so the code can't be read back from the
        // database; the plain code exists just long enough to be emailed.
        $user->forceFill([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => Carbon::now()->addMinutes(self::OTP_TTL_MINUTES),
        ])->save();

        try {
            Mail::to($user->email)->send(new OtpCodeMail($otp));
        } catch (\Throwable $e) {
            // Don't let a mail failure break registration/login — log it and
            // keep going. The code itself must never be written to the logs.
            Log::error("Failed to send OTP email to {$user->email}: ".$e->getMessage());
        }

        return $otp;
    }

    public function verify(User $user, string $otp): bool
    {
        if (! $this->isValid($user, $otp)) {
            return false;
        }

        $user->forceFill([
            'is_verified' => true,
            'email_verified_at' => Carbon::now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        return true;
    }

    /**
     * Checks the code without consuming it or marking the user verified —
     * used by the password-reset flow, which needs to check the same code
     * twice (once to let the UI give instant feedback, once again when the
     * new password is actually submitted) without side effects until the
     * password is actually changed.
     */
    public function isValid(User $user, string $otp): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at) {
            return false;
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return false;
        }

        return Hash::check($otp, $user->otp_code);
    }

    public function clear(User $user): void
    {
        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();
    }
}