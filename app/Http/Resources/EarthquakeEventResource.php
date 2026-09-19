<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarthquakeEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'magnitude' => (float) $this->magnitude,
            'depth_km' => (float) $this->depth_km,
            'location_name' => $this->location_name,
            'city' => new CityResource($this->whenLoaded('city')),
            'processed' => (bool) $this->processed,
            'occurred_at' => $this->occurred_at,
        ];
    }
}
