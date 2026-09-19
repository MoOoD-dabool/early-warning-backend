<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class City extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'cities';

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'city_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'city_id');
    }

    public function earthquakeEvents(): HasMany
    {
        return $this->hasMany(EarthquakeEvent::class, 'city_id');
    }

    public function weatherReadings(): HasMany
    {
        return $this->hasMany(WeatherReading::class, 'city_id');
    }
}