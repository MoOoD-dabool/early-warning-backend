<?php

namespace App\Filament\Resources\Admins\Tables;

use App\Models\Admin;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state) => $state === Admin::ROLE_SUPER_ADMIN ? 'warning' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === Admin::ROLE_SUPER_ADMIN ? 'Super Admin' : 'Admin'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                // No bulk-delete here on purpose — this table only has
                // per-row delete, which respects AdminResource::canDelete()
                // (blocks deleting your own account); a bulk action
                // wouldn't get that same per-record safety check.
                DeleteAction::make(),
            ]);
    }
}
