<?php

namespace App\Filament\Resources\ReliefTeams\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReliefTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')
                    ->relationship('city', 'name_ar')
                    ->required(),
                TextInput::make('street')
                    ->required(),
                TextInput::make('building_number')
                    ->required(),
                Select::make('status')
                    ->options(collect(config('relief_teams.statuses'))
                        ->map(fn ($s) => $s['label_ar'])
                        ->all())
                    ->default('active')
                    ->required(),
                Select::make('relief_type')
                    ->label('Relief type')
                    ->options(collect(config('relief_teams.types'))
                        ->map(fn ($t) => $t['label_ar'])
                        ->all())
                    ->nullable(),
                Select::make('disaster_type_id')
                    ->label('Disaster (event)')
                    ->relationship('disasterType', 'name_ar')
                    ->nullable(),
                DateTimePicker::make('deployed_at')
                    ->label('Deployed at')
                    ->nullable(),
            ]);
    }
}
