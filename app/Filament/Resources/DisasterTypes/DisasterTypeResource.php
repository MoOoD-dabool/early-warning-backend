<?php

namespace App\Filament\Resources\DisasterTypes;

use App\Filament\Resources\DisasterTypes\Pages\CreateDisasterType;
use App\Filament\Resources\DisasterTypes\Pages\EditDisasterType;
use App\Filament\Resources\DisasterTypes\Pages\ListDisasterTypes;
use App\Filament\Resources\DisasterTypes\Schemas\DisasterTypeForm;
use App\Filament\Resources\DisasterTypes\Tables\DisasterTypesTable;
use App\Models\DisasterType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DisasterTypeResource extends Resource
{
    protected static ?string $model = DisasterType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function form(Schema $schema): Schema
    {
        return DisasterTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DisasterTypesTable::configure($table);
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
            'index' => ListDisasterTypes::route('/'),
            'create' => CreateDisasterType::route('/create'),
            'edit' => EditDisasterType::route('/{record}/edit'),
        ];
    }
}
