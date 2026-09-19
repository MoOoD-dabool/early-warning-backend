<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeatherReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'city' => new CityResource($this->whenLoaded('city')),
            'temperature_c' => $this->temperature_c,
            'humidity_percent' => $this->humidity_percent,
            'wind_speed_kmh' => $this->wind_speed_kmh,
            'pressure_msl' => $this->pressure_msl,
            'precipitation_mm' => $this->precipitation_mm,
            'weather_code' => $this->weather_code,
            'fetched_at' => $this->fetched_at,
        ];
    }
}