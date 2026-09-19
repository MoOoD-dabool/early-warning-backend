<?php

namespace App\Filament\Resources\ReliefRequests\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReliefRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.email')
                    ->label('Requested by')
                    ->searchable(),
                TextColumn::make('city.name_ar')
                    ->label('City')
                    ->searchable(),
                TextColumn::make('street')
                    ->searchable(),
                TextColumn::make('building_number')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
