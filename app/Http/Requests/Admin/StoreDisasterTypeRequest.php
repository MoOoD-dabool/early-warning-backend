<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisasterTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:disaster_types,key'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'instructions_before_ar' => ['required', 'string'],
            'instructions_before_en' => ['required', 'string'],
            'instructions_during_ar' => ['required', 'string'],
            'instructions_during_en' => ['required', 'string'],
            'instructions_after_ar' => ['required', 'string'],
            'instructions_after_en' => ['required', 'string'],
            'audio_file_ar' => ['sometimes', 'file', 'mimes:mp3,wav,ogg', 'max:10240'],
            'audio_file_en' => ['sometimes', 'file', 'mimes:mp3,wav,ogg', 'max:10240'],
        ];
    }
}