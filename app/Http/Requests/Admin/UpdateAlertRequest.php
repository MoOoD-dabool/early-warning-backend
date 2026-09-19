<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disaster_type_id' => ['sometimes', 'integer', 'exists:disaster_types,id'],
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'earthquake_event_id' => ['nullable', 'integer', 'exists:earthquake_events,id'],
            'severity' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'message_ar' => ['sometimes', 'string', 'max:2000'],
            'message_en' => ['sometimes', 'string', 'max:2000'],
            'issued_at' => ['sometimes', 'date'],
        ];
    }
}