<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // A Google-only account (see AuthController::googleAuth) has no
        // password yet, so there's nothing to confirm when setting the
        // first one — current_password is only required once a password
        // actually exists.
        $hasPassword = $this->user()->password !== null;

        return [
            'current_password' => $hasPassword ? ['required', 'string'] : ['prohibited'],
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