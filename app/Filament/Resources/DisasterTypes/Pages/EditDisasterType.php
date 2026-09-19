<?php

namespace App\Filament\Resources\DisasterTypes\Pages;

use App\Filament\Resources\DisasterTypes\DisasterTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDisasterType extends EditRecord
{
    protected static string $resource = DisasterTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
