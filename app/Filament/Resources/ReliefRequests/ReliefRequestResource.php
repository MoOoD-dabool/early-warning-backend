<?php

namespace App\Filament\Resources\ReliefRequests;

use App\Filament\Resources\ReliefRequests\Pages\ListReliefRequests;
use App\Filament\Resources\ReliefRequests\Schemas\ReliefRequestForm;
use App\Filament\Resources\ReliefRequests\Tables\ReliefRequestsTable;
use App\Models\ReliefRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReliefRequestResource extends Resource
{
    protected static ?string $model = ReliefRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReliefRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReliefRequestsTable::configure($table);
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
            'index' => ListReliefRequests::route('/'),
        ];
    }

    // Read-only, matching the existing admin API (GET index/show only —
    // relief requests only ever come from mobile users).
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
