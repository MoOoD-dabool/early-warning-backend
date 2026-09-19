<?php

namespace App\Filament\Resources\ReliefRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReliefRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->label('Requested by')
                    ->disabled(),
                Select::make('city_id')
                    ->relationship('city', 'name_ar')
                    ->label('City')
                    ->disabled(),
                TextInput::make('street')->disabled(),
                TextInput::make('building_number')->disabled(),
            ]);
    }
}
