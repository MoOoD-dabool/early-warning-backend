<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp_code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'regex:'.self::PASSWORD_REGEX, 'confirmed'],
        ];
    }

    // 8-24 chars, English letters + digits only, at least one letter and
    // one digit — kept in sync with the Flutter app's isValidPassword().
    private const PASSWORD_REGEX = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]{8,24}$/';

    public function messages(): array
    {
        return [
            'password.regex' => __('messages.auth.password_format'),
        ];
    }
}
