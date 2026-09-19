<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'city_code' => ['required', 'string', 'exists:cities,code'],
            'street_name' => ['required', 'string', 'max:255'],
            'building_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
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