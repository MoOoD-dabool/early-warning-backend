<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('causer.name')
                    ->label('Admin')
                    ->default('System')
                    ->disabled(),
                TextInput::make('event')->disabled(),
                TextInput::make('subject_type')->disabled(),
                TextInput::make('subject_id')->numeric()->disabled(),
                Textarea::make('description')->disabled()->columnSpanFull(),
                // Raw before/after values for the changed fields — enough
                // detail to see exactly what an admin changed.
                Textarea::make('properties')
                    ->label('Changes')
                    ->disabled()
                    ->columnSpanFull()
                    ->rows(8),
                TextInput::make('created_at')->disabled(),
            ]);
    }
}
