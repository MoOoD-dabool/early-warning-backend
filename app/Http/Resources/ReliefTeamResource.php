<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReliefTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city' => new CityResource($this->whenLoaded('city')),
            'street' => $this->street,
            'building_number' => $this->building_number,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
