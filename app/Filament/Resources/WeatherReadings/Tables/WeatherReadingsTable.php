<?php

namespace App\Filament\Resources\WeatherReadings\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WeatherReadingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fetched_at', 'desc')
            ->columns([
                TextColumn::make('city.name_ar')->label('City')->searchable(),
                TextColumn::make('temperature_c')->label('Temp (°C)')->numeric(1),
                TextColumn::make('humidity_percent')->label('Humidity (%)'),
                TextColumn::make('wind_speed_kmh')->label('Wind (km/h)')->numeric(1),
                TextColumn::make('pressure_msl')->label('Pressure (hPa)')->numeric(1),
                TextColumn::make('precipitation_mm')->label('Rain (mm/h)')->numeric(1),
                TextColumn::make('weather_code')->label('WMO code'),
                TextColumn::make('fetched_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
