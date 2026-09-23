<?php

namespace App\Filament\Resources\EarthquakeEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EarthquakeEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_id')
                    ->required(),
                TextInput::make('magnitude')
                    ->required()
                    ->numeric(),
                TextInput::make('depth_km')
                    ->required()
                    ->numeric(),
                TextInput::make('location_name')
                    ->required(),
                Select::make('city_id')
                    ->relationship('city', 'name_ar'),
                Toggle::make('processed')
                    ->required(),
                DateTimePicker::make('occurred_at')
                    ->required(),
            ]);
    }
}
