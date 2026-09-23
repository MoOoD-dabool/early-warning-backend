<?php

namespace App\Filament\Resources\WeatherReadings;

use App\Filament\Resources\WeatherReadings\Pages\ListWeatherReadings;
use App\Filament\Resources\WeatherReadings\Schemas\WeatherReadingForm;
use App\Filament\Resources\WeatherReadings\Tables\WeatherReadingsTable;
use App\Models\WeatherReading;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeatherReadingResource extends Resource
{
    protected static ?string $model = WeatherReading::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WeatherReadingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeatherReadingsTable::configure($table);
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
            'index' => ListWeatherReadings::route('/'),
        ];
    }

    // Read-only, purely for troubleshooting ("why didn't an alert fire for
    // this city?") - readings only ever come from weather:fetch, never a
    // panel form.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }
}
