<?php

namespace App\Filament\Resources\UserAlerts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable(),
                TextColumn::make('alert.city.name_ar')->label('City')->searchable(),
                TextColumn::make('alert.disasterType.name_ar')->label('Disaster type')->searchable(),
                TextColumn::make('alert.severity')->label('Severity')->badge(),
                TextColumn::make('received_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
