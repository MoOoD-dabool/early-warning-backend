<?php

namespace App\Filament\Resources\FeltReports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Schema;

class FeltReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')->relationship('user', 'email')->label('Reported by')->disabled(),
                Select::make('city_id')->relationship('city', 'name_ar')->label('City')->disabled(),
                Select::make('earthquake_event_id')->relationship('earthquakeEvent', 'event_id')->label('Earthquake')->disabled(),
                TagsInput::make('intensity_levels')->label('Felt intensity levels')->disabled(),
            ]);
    }
}
