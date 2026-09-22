<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReliefTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'street' => ['sometimes', 'string', 'max:255'],
            'building_number' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', array_keys(config('relief_teams.statuses')))],
            'relief_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('relief_teams.types')))],
            'disaster_type_id' => ['nullable', 'integer', 'exists:disaster_types,id'],
            'deployed_at' => ['nullable', 'date'],
        ];
    }
}
