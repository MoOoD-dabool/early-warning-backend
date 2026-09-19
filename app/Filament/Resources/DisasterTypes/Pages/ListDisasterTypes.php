<?php

namespace App\Filament\Resources\DisasterTypes\Pages;

use App\Filament\Resources\DisasterTypes\DisasterTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDisasterTypes extends ListRecords
{
    protected static string $resource = DisasterTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
