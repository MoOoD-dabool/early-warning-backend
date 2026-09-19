<?php

namespace App\Filament\Resources\EarthquakeEvents\Pages;

use App\Filament\Resources\EarthquakeEvents\EarthquakeEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEarthquakeEvents extends ListRecords
{
    protected static string $resource = EarthquakeEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
