<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Alert extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'alerts';

    protected $fillable = [
        'disaster_type_id',
        'city_id',
        'earthquake_event_id',
        'severity',
        'trigger_siren',
        'message_ar',
        'message_en',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger_siren' => 'boolean',
            'issued_at' => 'datetime',
        ];
    }

    // Also logs alerts created automatically by the earthquake/weather
    // background jobs (with no causer, since no admin is logged in for
    // those) — useful to tell those apart from ones an admin created or
    // edited manually.
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function disasterType(): BelongsTo
    {
        return $this->belongsTo(DisasterType::class, 'disaster_type_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function earthquakeEvent(): BelongsTo
    {
        return $this->belongsTo(EarthquakeEvent::class, 'earthquake_event_id');
    }

    public function userAlerts(): HasMany
    {
        return $this->hasMany(UserAlert::class, 'alert_id');
    }
}