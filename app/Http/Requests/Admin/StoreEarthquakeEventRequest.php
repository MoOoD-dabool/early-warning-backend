<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEarthquakeEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string', 'max:255', 'unique:earthquake_events,event_id'],
            'magnitude' => ['required', 'numeric', 'between:0,10'],
            'depth_km' => ['required', 'numeric', 'min:0'],
            'location_name' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'occurred_at' => ['required', 'date'],
            'auto_create_alert' => ['sometimes', 'boolean'],
            'disaster_type_id' => ['required_if:auto_create_alert,true', 'integer', 'exists:disaster_types,id'],
        ];
    }
}
