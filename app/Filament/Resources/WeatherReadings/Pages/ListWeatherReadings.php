<?php

namespace App\Filament\Resources\WeatherReadings\Pages;

use App\Filament\Resources\WeatherReadings\WeatherReadingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWeatherReadings extends ListRecords
{
    protected static string $resource = WeatherReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
