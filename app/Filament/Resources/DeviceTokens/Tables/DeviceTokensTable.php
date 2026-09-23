<?php

namespace App\Filament\Resources\DeviceTokens\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeviceTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable(),
                TextColumn::make('platform')->label('Platform'),
                // Only the last 6 characters — the token itself has no
                // value outside FCM, this is just enough to eyeball
                // "does this look like a real registered token".
                TextColumn::make('token')
                    ->label('Token (last 6 chars)')
                    ->formatStateUsing(fn (?string $state): string => $state ? '…'.substr($state, -6) : '—'),
                IconColumn::make('sound_enabled')->label('Sound on')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
