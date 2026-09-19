<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisasterTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('disaster_types', 'key')->ignore($this->route('disaster_type'))],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'instructions_before_ar' => ['sometimes', 'string'],
            'instructions_before_en' => ['sometimes', 'string'],
            'instructions_during_ar' => ['sometimes', 'string'],
            'instructions_during_en' => ['sometimes', 'string'],
            'instructions_after_ar' => ['sometimes', 'string'],
            'instructions_after_en' => ['sometimes', 'string'],
            'audio_file_ar' => ['sometimes', 'file', 'mimes:mp3,wav,ogg', 'max:10240'],
            'audio_file_en' => ['sometimes', 'file', 'mimes:mp3,wav,ogg', 'max:10240'],
        ];
    }
}