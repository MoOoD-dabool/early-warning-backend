<?php

namespace App\Filament\Resources\EarthquakeEvents\Pages;

use App\Filament\Resources\EarthquakeEvents\EarthquakeEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEarthquakeEvent extends EditRecord
{
    protected static string $resource = EarthquakeEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
