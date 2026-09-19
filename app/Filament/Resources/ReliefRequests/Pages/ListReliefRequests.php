<?php

namespace App\Filament\Resources\ReliefRequests\Pages;

use App\Filament\Resources\ReliefRequests\ReliefRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReliefRequests extends ListRecords
{
    protected static string $resource = ReliefRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
