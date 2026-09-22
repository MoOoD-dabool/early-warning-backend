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
            'status' => $this->status,
            'status_label' => [
                'ar' => config("relief_teams.statuses.{$this->status}.label_ar"),
                'en' => config("relief_teams.statuses.{$this->status}.label_en"),
            ],
            'relief_type' => $this->relief_type,
            'relief_type_label' => $this->relief_type === null ? null : [
                'ar' => config("relief_teams.types.{$this->relief_type}.label_ar"),
                'en' => config("relief_teams.types.{$this->relief_type}.label_en"),
            ],
            'disaster_type' => $this->whenLoaded('disasterType', fn () => $this->disasterType === null ? null : [
                'key' => $this->disasterType->key,
                'name' => [
                    'ar' => $this->disasterType->name_ar,
                    'en' => $this->disasterType->name_en,
                ],
            ]),
            'deployed_at' => $this->deployed_at?->toIso8601String(),
        ];
    }
}
