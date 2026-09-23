<?php

namespace App\Filament\Resources\UserAlerts;

use App\Filament\Resources\UserAlerts\Pages\ListUserAlerts;
use App\Filament\Resources\UserAlerts\Schemas\UserAlertForm;
use App\Filament\Resources\UserAlerts\Tables\UserAlertsTable;
use App\Models\UserAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserAlertResource extends Resource
{
    protected static ?string $model = UserAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserAlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAlertsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserAlerts::route('/'),
        ];
    }

    // Read-only, purely for troubleshooting ("did this alert actually
    // reach this user?") - rows only ever come from
    // AlertDispatchService::dispatch(), never a panel form.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }
}
