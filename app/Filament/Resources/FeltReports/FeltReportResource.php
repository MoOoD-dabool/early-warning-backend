<?php

namespace App\Filament\Resources\FeltReports;

use App\Filament\Resources\FeltReports\Pages\ListFeltReports;
use App\Filament\Resources\FeltReports\Schemas\FeltReportForm;
use App\Filament\Resources\FeltReports\Tables\FeltReportsTable;
use App\Models\FeltReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeltReportResource extends Resource
{
    protected static ?string $model = FeltReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    public static function form(Schema $schema): Schema
    {
        return FeltReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeltReportsTable::configure($table);
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
            'index' => ListFeltReports::route('/'),
        ];
    }

    // Read-only, matching the mobile API (POST from the user, GET index/show
    // for the admin only — no way for an admin to create/edit/delete one).
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
