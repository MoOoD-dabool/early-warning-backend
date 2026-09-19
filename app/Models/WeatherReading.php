<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherReading extends Model
{
    use HasFactory;

    protected $table = 'weather_readings';

    protected $fillable = [
        'city_id',
        'temperature_c',
        'humidity_percent',
        'wind_speed_kmh',
        'pressure_msl',
        'precipitation_mm',
        'weather_code',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature_c' => 'float',
            'wind_speed_kmh' => 'float',
            'pressure_msl' => 'float',
            'precipitation_mm' => 'float',
            'humidity_percent' => 'integer',
            'weather_code' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}