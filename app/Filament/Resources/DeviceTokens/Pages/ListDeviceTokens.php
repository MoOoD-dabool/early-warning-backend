<?php

namespace App\Filament\Resources\DeviceTokens\Pages;

use App\Filament\Resources\DeviceTokens\DeviceTokenResource;
use Filament\Resources\Pages\ListRecords;

class ListDeviceTokens extends ListRecords
{
    protected static string $resource = DeviceTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create button: canCreate() is false for this resource, but in this
            // Filament version a header CreateAction ignores canCreate() and would
            // still open a create modal. Records here come from the system only.
        ];
    }
}
