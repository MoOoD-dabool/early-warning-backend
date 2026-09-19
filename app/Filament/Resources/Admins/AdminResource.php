<?php

namespace App\Filament\Resources\Admins;

use App\Filament\Resources\Admins\Pages\CreateAdmin;
use App\Filament\Resources\Admins\Pages\EditAdmin;
use App\Filament\Resources\Admins\Pages\ListAdmins;
use App\Filament\Resources\Admins\Schemas\AdminForm;
use App\Filament\Resources\Admins\Tables\AdminsTable;
use App\Models\Admin;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Managing other admin accounts is restricted to super_admin — a plain
 * admin can't even see this resource in the sidebar (canViewAny governs
 * that too), let alone create/edit/delete accounts here.
 */
class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
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
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }

    private static function currentAdminIsSuperAdmin(): bool
    {
        /** @var Admin|null $admin */
        $admin = Filament::auth()->user();

        return (bool) $admin?->isSuperAdmin();
    }

    public static function canViewAny(): bool
    {
        return self::currentAdminIsSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return self::currentAdminIsSuperAdmin();
    }

    public static function canEdit(mixed $record): bool
    {
        return self::currentAdminIsSuperAdmin();
    }

    public static function canDelete(mixed $record): bool
    {
        // Also block deleting your own account, even as a super_admin —
        // otherwise a super_admin could accidentally lock themselves out.
        return self::currentAdminIsSuperAdmin()
            && $record->id !== Filament::auth()->id();
    }
}
