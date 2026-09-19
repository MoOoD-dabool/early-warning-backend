<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            // Null for a Google account that hasn't completed its profile yet
            // (see AuthController::googleAuth) — whenLoaded() alone would pass
            // a genuinely-null relation into CityResource and try to read
            // properties off it.
            'city' => $this->city ? new CityResource($this->city) : null,
            'street_name' => $this->street_name,
            'building_number' => $this->building_number,
            'profile_image' => $this->profile_image ? asset('storage/'.$this->profile_image) : null,
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at,
        ];
    }
}
