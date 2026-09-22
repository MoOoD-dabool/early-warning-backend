<?php

namespace App\Filament\Resources\ReliefTeams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReliefTeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('city.id')
                    ->searchable(),
                TextColumn::make('street')
                    ->searchable(),
                TextColumn::make('building_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => config("relief_teams.statuses.{$state}.label_ar", $state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'en_route' => 'warning',
                        'completed' => 'gray',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('relief_type')
                    ->formatStateUsing(fn (?string $state): string => $state === null
                        ? '-'
                        : config("relief_teams.types.{$state}.label_ar", $state)),
                TextColumn::make('disasterType.name_ar')
                    ->label('Disaster')
                    ->placeholder('-'),
                TextColumn::make('deployed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
