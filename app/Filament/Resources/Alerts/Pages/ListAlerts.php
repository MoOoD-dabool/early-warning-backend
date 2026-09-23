<?php

namespace App\Filament\Resources\Alerts\Pages;

use App\Filament\Resources\Alerts\AlertResource;
use Filament\Resources\Pages\ListRecords;

class ListAlerts extends ListRecords
{
    protected static string $resource = AlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create button: canCreate() is false for this resource, but in this
            // Filament version a header CreateAction ignores canCreate() and would
            // still open a create modal. Records here come from the system only.
        ];
    }
}
