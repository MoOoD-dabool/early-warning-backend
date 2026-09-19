<?php

namespace App\Filament\Resources\EarthquakeEvents;

use App\Filament\Resources\EarthquakeEvents\Pages\CreateEarthquakeEvent;
use App\Filament\Resources\EarthquakeEvents\Pages\EditEarthquakeEvent;
use App\Filament\Resources\EarthquakeEvents\Pages\ListEarthquakeEvents;
use App\Filament\Resources\EarthquakeEvents\Schemas\EarthquakeEventForm;
use App\Filament\Resources\EarthquakeEvents\Tables\EarthquakeEventsTable;
use App\Models\EarthquakeEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EarthquakeEventResource extends Resource
{
    protected static ?string $model = EarthquakeEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EarthquakeEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EarthquakeEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEarthquakeEvents::route('/'),
            'create' => CreateEarthquakeEvent::route('/create'),
            'edit' => EditEarthquakeEvent::route('/{record}/edit'),
        ];
    }
}
