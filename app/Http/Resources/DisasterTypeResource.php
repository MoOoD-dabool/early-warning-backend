<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisasterTypeResource extends JsonResource
{
    /**
     * Returns both languages for every text field so the Flutter app can
     * switch instantly when the user changes the language from settings,
     * without needing a new request to the API.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'instructions_before' => [
                'ar' => $this->instructions_before_ar,
                'en' => $this->instructions_before_en,
            ],
            'instructions_during' => [
                'ar' => $this->instructions_during_ar,
                'en' => $this->instructions_during_en,
            ],
            'instructions_after' => [
                'ar' => $this->instructions_after_ar,
                'en' => $this->instructions_after_en,
            ],
            'audio_file' => [
                'ar' => $this->audio_file_ar ? asset('storage/'.$this->audio_file_ar) : null,
                'en' => $this->audio_file_en ? asset('storage/'.$this->audio_file_en) : null,
            ],
        ];
    }
}