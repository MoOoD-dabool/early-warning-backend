<?php

namespace App\Filament\Resources\EarthquakeEvents\Pages;

use App\Filament\Resources\EarthquakeEvents\EarthquakeEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEarthquakeEvent extends CreateRecord
{
    protected static string $resource = EarthquakeEventResource::class;
}
