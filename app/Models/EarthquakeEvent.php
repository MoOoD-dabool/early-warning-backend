<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarthquakeEvent extends Model
{
    use HasFactory;

    protected $table = 'earthquake_events';

    protected $fillable = [
        'event_id',
        'magnitude',
        'depth_km',
        'location_name',
        'city_id',
        'processed',
        'occurred_at',
    ];


    protected function casts(): array
    {
        return [
            'magnitude' => 'decimal:2',
            'depth_km' => 'decimal:2',
            'processed' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'earthquake_event_id');
    }

    public function feltReports(): HasMany
    {
        return $this->hasMany(FeltReport::class, 'earthquake_event_id');
    }
}
