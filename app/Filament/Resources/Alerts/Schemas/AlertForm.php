<?php

namespace App\Filament\Resources\Alerts\Schemas;

use App\Models\EarthquakeEvent;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
                    ->relationship('disasterType', 'name_ar')
                    ->required(),
                Select::make('city_id')
                    ->relationship('city', 'name_ar')
                    ->required(),
                Select::make('earthquake_event_id')
                    ->relationship('earthquakeEvent', 'location_name')
                    ->getOptionLabelFromRecordUsing(fn (EarthquakeEvent $record): string => sprintf(
                        'M%s — %s — %s',
                        $record->magnitude,
                        $record->location_name,
                        $record->occurred_at?->format('Y-m-d H:i'),
                    )),
                Select::make('severity')
                    ->options([
                        'low' => 'low',
                        'medium' => 'medium',
                        'high' => 'high',
                        'critical' => 'critical',
                    ])
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
