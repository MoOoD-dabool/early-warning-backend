<?php

namespace App\Filament\Resources\EarthquakeEvents;

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
            'edit' => EditEarthquakeEvent::route('/{record}/edit'),
        ];
    }

    // A hand-typed row here skips EmscEarthquakeProcessor entirely (no
    // affected-cities calc, no alerts, no push) while still showing
    // "created successfully" - a real, misleading trap. Any real record
    // (live or simulated) always goes through the real pipeline (the
    // listener, or the disaster simulator using real EMSC values), which
    // is more accurate than hand-typed values anyway.
    public static function canCreate(): bool
    {
        return false;
    }
}
