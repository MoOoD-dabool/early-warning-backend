<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disaster_type_id' => ['required', 'integer', 'exists:disaster_types,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'earthquake_event_id' => ['nullable', 'integer', 'exists:earthquake_events,id'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'message_ar' => ['required', 'string', 'max:2000'],
            'message_en' => ['required', 'string', 'max:2000'],
            'issued_at' => ['sometimes', 'date'],
        ];
    }
}