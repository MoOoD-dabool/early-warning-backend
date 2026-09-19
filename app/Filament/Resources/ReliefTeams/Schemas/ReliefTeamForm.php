<?php

namespace App\Filament\Resources\ReliefTeams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReliefTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')
                    ->relationship('city', 'id')
                    ->required(),
                TextInput::make('street')
                    ->required(),
                TextInput::make('building_number')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
