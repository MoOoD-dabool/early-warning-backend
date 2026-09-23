<?php

namespace App\Filament\Resources\DeviceTokens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeviceTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')->relationship('user', 'email')->label('User')->disabled(),
                TextInput::make('platform')->disabled(),
                // Same last-6-chars masking as the table - the raw token
                // has no legitimate use here.
                TextInput::make('token')
                    ->label('Token (last 6 chars)')
                    ->formatStateUsing(fn (?string $state): string => $state ? '…'.substr($state, -6) : '—')
                    ->disabled(),
                Toggle::make('sound_enabled')->label('Sound enabled')->disabled(),
                TextInput::make('created_at')->label('Registered at')->disabled(),
            ]);
    }
}
