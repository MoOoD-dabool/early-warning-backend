<?php

namespace App\Filament\Resources\DeviceTokens;

use App\Filament\Resources\DeviceTokens\Pages\ListDeviceTokens;
use App\Filament\Resources\DeviceTokens\Schemas\DeviceTokenForm;
use App\Filament\Resources\DeviceTokens\Tables\DeviceTokensTable;
use App\Models\DeviceToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeviceTokenResource extends Resource
{
    protected static ?string $model = DeviceToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DeviceTokenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeviceTokensTable::configure($table);
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
            'index' => ListDeviceTokens::route('/'),
        ];
    }

    // Read-only, purely for troubleshooting ("does this user even have a
    // registered device?") - tokens only ever come from the mobile app
    // itself via POST/DELETE /device-tokens, never a panel form.
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
