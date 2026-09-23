<?php

namespace App\Filament\Resources\UserAlerts\Pages;

use App\Filament\Resources\UserAlerts\UserAlertResource;
use Filament\Resources\Pages\ListRecords;

class ListUserAlerts extends ListRecords
{
    protected static string $resource = UserAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create button: canCreate() is false for this resource, but in this
            // Filament version a header CreateAction ignores canCreate() and would
            // still open a create modal. Records here come from the system only.
        ];
    }
}
