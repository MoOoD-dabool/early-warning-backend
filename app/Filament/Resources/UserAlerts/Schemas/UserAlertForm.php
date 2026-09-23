<?php

namespace App\Filament\Resources\UserAlerts\Schemas;

use App\Models\UserAlert;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserAlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')->relationship('user', 'email')->label('User')->disabled(),
                TextInput::make('alert_city')
                    ->label('City')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn (?UserAlert $record): ?string => $record?->alert?->city?->name_ar),
                TextInput::make('alert_disaster_type')
                    ->label('Disaster type')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn (?UserAlert $record): ?string => $record?->alert?->disasterType?->name_ar),
                TextInput::make('alert_severity')
                    ->label('Severity')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn (?UserAlert $record): ?string => $record?->alert?->severity),
                Textarea::make('alert_message')
                    ->label('Message sent')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn (?UserAlert $record): ?string => $record?->alert?->message_ar),
                TextInput::make('received_at')->label('Received at')->disabled(),
            ]);
    }
}
