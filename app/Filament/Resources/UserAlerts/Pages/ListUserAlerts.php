<?php

namespace App\Filament\Resources\UserAlerts\Pages;

use App\Filament\Resources\UserAlerts\UserAlertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAlerts extends ListRecords
{
    protected static string $resource = UserAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
