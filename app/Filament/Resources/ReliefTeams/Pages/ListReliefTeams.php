<?php

namespace App\Filament\Resources\ReliefTeams\Pages;

use App\Filament\Resources\ReliefTeams\ReliefTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReliefTeams extends ListRecords
{
    protected static string $resource = ReliefTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
