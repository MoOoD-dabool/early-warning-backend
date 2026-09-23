<?php

namespace App\Filament\Resources\ReliefRequests\Pages;

use App\Filament\Resources\ReliefRequests\ReliefRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListReliefRequests extends ListRecords
{
    protected static string $resource = ReliefRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create button: canCreate() is false for this resource, but in this
            // Filament version a header CreateAction ignores canCreate() and would
            // still open a create modal. Records here come from the system only.
        ];
    }
}
