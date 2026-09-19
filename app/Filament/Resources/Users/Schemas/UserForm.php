<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Read-only by design (see UserResource — no create/edit pages exist, only
 * ViewAction), matching the existing admin API which only ever exposed
 * GET /admin/users, never editing. Sensitive/internal fields
 * (password, otp_code, otp_expires_at) are intentionally left out.
 */
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')->disabled(),
                TextInput::make('last_name')->disabled(),
                Select::make('city_id')
                    ->relationship('city', 'name_ar')
                    ->label('City')
                    ->disabled(),
                TextInput::make('street_name')->disabled(),
                TextInput::make('building_number')->disabled(),
                TextInput::make('email')->email()->disabled(),
                DateTimePicker::make('email_verified_at')->disabled(),
                Toggle::make('is_verified')->disabled(),
                TextInput::make('google_id')
                    ->label('Google account')
                    ->disabled(),
            ]);
    }
}
