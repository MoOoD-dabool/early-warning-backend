<?php

namespace App\Filament\Resources\ReliefTeams\Pages;

use App\Filament\Resources\ReliefTeams\ReliefTeamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReliefTeam extends EditRecord
{
    protected static string $resource = ReliefTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
