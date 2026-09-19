<?php

namespace App\Filament\Resources\ReliefTeams;

use App\Filament\Resources\ReliefTeams\Pages\CreateReliefTeam;
use App\Filament\Resources\ReliefTeams\Pages\EditReliefTeam;
use App\Filament\Resources\ReliefTeams\Pages\ListReliefTeams;
use App\Filament\Resources\ReliefTeams\Schemas\ReliefTeamForm;
use App\Filament\Resources\ReliefTeams\Tables\ReliefTeamsTable;
use App\Models\ReliefTeam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReliefTeamResource extends Resource
{
    protected static ?string $model = ReliefTeam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReliefTeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReliefTeamsTable::configure($table);
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
            'index' => ListReliefTeams::route('/'),
            'create' => CreateReliefTeam::route('/create'),
            'edit' => EditReliefTeam::route('/{record}/edit'),
        ];
    }
}
