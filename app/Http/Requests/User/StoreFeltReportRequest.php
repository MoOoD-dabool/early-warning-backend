<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeltReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'intensity_levels' => ['required', 'array', 'min:1', 'max:3'],
            'intensity_levels.*' => ['integer', Rule::in(array_keys(config('mercalli.levels')))],
        ];
    }
}
