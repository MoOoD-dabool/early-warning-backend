<?php

namespace App\Filament\Resources\Alerts;

use App\Filament\Resources\Alerts\Pages\EditAlert;
use App\Filament\Resources\Alerts\Pages\ListAlerts;
use App\Filament\Resources\Alerts\Schemas\AlertForm;
use App\Filament\Resources\Alerts\Tables\AlertsTable;
use App\Models\Alert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlertsTable::configure($table);
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
            'index' => ListAlerts::route('/'),
            'edit' => EditAlert::route('/{record}/edit'),
        ];
    }

    // A hand-typed row here skips AlertDispatchService entirely (no push,
    // no user_alerts rows) while still showing "created successfully" -
    // a real, misleading trap. Real alerts always go through Send Alert or
    // the disaster simulator, which dispatch properly; there's no
    // legitimate reason left to create one here by hand.
    public static function canCreate(): bool
    {
        return false;
    }
}
