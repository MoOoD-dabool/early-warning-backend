<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeltReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'city' => new CityResource($this->whenLoaded('city')),
            'earthquake_event' => new EarthquakeEventResource($this->whenLoaded('earthquakeEvent')),
            'intensity_levels' => $this->intensity_levels,
            'created_at' => $this->created_at,
        ];
    }
}
