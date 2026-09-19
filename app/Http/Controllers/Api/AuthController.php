<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\GoogleAuthRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\VerifyResetOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\City;
use App\Models\User;
use App\Services\GoogleTokenVerifier;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    /**
     * Step 1 + Step 2 of the mobile registration flow combined: creates the
     * user as unverified and sends an OTP code to confirm the email.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['city_id'] = City::query()->where('code', $data['city_code'])->value('id');
        unset($data['city_code']);
        $data['password'] = Hash::make($data['password']);
        $data['is_verified'] = false;

        $user = User::query()->create($data);

        $this->otpService->generateFor($user);

        return response()->json([
            'message' => __('messages.auth.register_success'),
            'user' => new UserResource($user),
        ], 201);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        if (! $this->otpService->verify($user, (string) $request->input('otp_code'))) {
            throw ValidationException::withMessages([
                'otp_code' => __('messages.auth.otp_invalid'),
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => __('messages.auth.account_verified'),
            'token' => $token,
            'user' => new UserResource($user->load('city')),
        ]);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        if ($user->is_verified) {
            return response()->json(['message' => __('messages.auth.already_verified')], 409);
        }

        $this->otpService->generateFor($user);

        return response()->json(['message' => __('messages.auth.otp_resent')]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        // A Google-only account (see googleAuth()) has no local password
        // until the user sets one — treat that exactly like a wrong
        // password instead of passing null into Hash::check().
        if (! $user || $user->password === null || ! Hash::check((string) $request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('messages.auth.invalid_credentials'),
            ]);
        }

        if (! $user->is_verified) {
            return response()->json([
                'message' => __('messages.auth.not_verified'),
                'requires_otp' => true,
            ], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('city')),
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::user()?->currentAccessToken()?->delete();

        return response()->json(['message' => __('messages.auth.logged_out')]);
    }

    /**
     * "Sign in with Google": verifies the ID token the Flutter app got from
     * google_sign_in, then either logs in an existing account (matching by
     * google_id first, then by email — so a user who already registered
     * normally can start using Google sign-in on the same account) or
     * creates a new one. Google already verified the email, so a new
     * account is marked verified immediately with no OTP step. A new
     * account has no city/street/building/password yet — the Flutter app
     * checks for that (city is null) and sends the user to a short
     * "complete your profile" screen before they can do much else.
     */
    public function googleAuth(GoogleAuthRequest $request, GoogleTokenVerifier $verifier): JsonResponse
    {
        $payload = $verifier->verify((string) $request->string('id_token'));

        if (! $payload) {
            throw ValidationException::withMessages([
                'id_token' => __('messages.auth.google_token_invalid'),
            ]);
        }

        $user = User::query()->where('google_id', $payload['sub'])->first()
            ?? User::query()->where('email', $payload['email'])->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $payload['sub']]);
            }
        } else {
            $user = User::query()->create([
                'google_id' => $payload['sub'],
                'first_name' => $payload['given_name'],
                'last_name' => $payload['family_name'],
                'email' => $payload['email'],
                'is_verified' => true,
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('city')),
        ]);
    }

    /**
     * Sends a fresh reset code to the account's email. Reuses the same
     * otp_code/otp_expires_at columns and mailable as registration — a user
     * can only be mid-registration or mid-password-reset at once, never
     * both, so there's no conflict in sharing them.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        $this->otpService->generateFor($user);

        return response()->json(['message' => __('messages.auth.reset_code_sent')]);
    }

    /**
     * Lets the UI confirm the code is right before moving to the "set new
     * password" screen. Doesn't consume the code — it's checked again (and
     * only then consumed) when resetPassword() actually runs.
     */
    public function verifyResetOtp(VerifyResetOtpRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        if (! $this->otpService->isValid($user, (string) $request->input('otp_code'))) {
            throw ValidationException::withMessages([
                'otp_code' => __('messages.auth.reset_code_invalid'),
            ]);
        }

        return response()->json(['message' => __('messages.auth.reset_code_verified')]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        if (! $this->otpService->isValid($user, (string) $request->input('otp_code'))) {
            throw ValidationException::withMessages([
                'otp_code' => __('messages.auth.reset_code_invalid'),
            ]);
        }

        $user->forceFill(['password' => Hash::make((string) $request->input('password'))])->save();
        $this->otpService->clear($user);

        // Force re-login everywhere after a password reset.
        $user->tokens()->delete();

        return response()->json(['message' => __('messages.auth.password_reset')]);
    }
}