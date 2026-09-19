<?php

namespace App\Filament\Resources\FeltReports\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeltReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.email')->label('Reported by')->searchable(),
                TextColumn::make('city.name_ar')->label('City')->searchable(),
                TextColumn::make('earthquakeEvent.event_id')->label('Earthquake')->searchable(),
                TextColumn::make('earthquakeEvent.magnitude')->label('Magnitude'),
                TextColumn::make('intensity_levels')
                    ->label('Felt intensity')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
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
