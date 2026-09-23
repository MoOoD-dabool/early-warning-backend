<?php

namespace App\Filament\Resources\WeatherReadings\Pages;

use App\Filament\Resources\WeatherReadings\WeatherReadingResource;
use Filament\Resources\Pages\ListRecords;

class ListWeatherReadings extends ListRecords
{
    protected static string $resource = WeatherReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create button: canCreate() is false for this resource, but in this
            // Filament version a header CreateAction ignores canCreate() and would
            // still open a create modal. Records here come from the system only.
        ];
    }
}
