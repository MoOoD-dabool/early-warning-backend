<?php

namespace App\Filament\Resources\EarthquakeEvents\Pages;

use App\Filament\Resources\EarthquakeEvents\EarthquakeEventResource;
use Filament\Resources\Pages\ListRecords;

class ListEarthquakeEvents extends ListRecords
{
    protected static string $resource = EarthquakeEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create button: canCreate() is false for this resource, but in this
            // Filament version a header CreateAction ignores canCreate() and would
            // still open a create modal. Records here come from the system only.
        ];
    }
}
