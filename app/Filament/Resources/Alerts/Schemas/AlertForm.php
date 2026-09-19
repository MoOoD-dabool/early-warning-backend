<?php

namespace App\Filament\Resources\Alerts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('disaster_type_id')
                    ->relationship('disasterType', 'id')
                    ->required(),
                Select::make('city_id')
                    ->relationship('city', 'id')
                    ->required(),
                Select::make('earthquake_event_id')
                    ->relationship('earthquakeEvent', 'id'),
                TextInput::make('severity')
                    ->required(),
                Toggle::make('trigger_siren')
                    ->required(),
                Textarea::make('message_ar')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('message_en')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('issued_at')
                    ->required(),
            ]);
    }
}
