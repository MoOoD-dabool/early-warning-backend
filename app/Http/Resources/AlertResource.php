<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'severity' => $this->severity,
            'trigger_siren' => (bool) $this->trigger_siren,
            'message' => [
                'ar' => $this->message_ar,
                'en' => $this->message_en,
            ],
            'issued_at' => $this->issued_at,
            'disaster_type' => new DisasterTypeResource($this->whenLoaded('disasterType')),
            'city' => new CityResource($this->whenLoaded('city')),
            'earthquake_event' => new EarthquakeEventResource($this->whenLoaded('earthquakeEvent')),
        ];
    }
}