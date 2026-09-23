<?php

namespace App\Filament\Resources\WeatherReadings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WeatherReadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')->relationship('city', 'name_ar')->label('City')->disabled(),
                TextInput::make('temperature_c')->label('Temperature (°C)')->disabled(),
                TextInput::make('humidity_percent')->label('Humidity (%)')->disabled(),
                TextInput::make('wind_speed_kmh')->label('Wind speed (km/h)')->disabled(),
                TextInput::make('pressure_msl')->label('Pressure (hPa)')->disabled(),
                TextInput::make('precipitation_mm')->label('Precipitation (mm/h)')->disabled(),
                TextInput::make('weather_code')->label('WMO weather code')->disabled(),
                TextInput::make('fetched_at')->label('Fetched at')->disabled(),
            ]);
    }
}
