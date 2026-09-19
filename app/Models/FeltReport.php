<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeltReport extends Model
{
    use HasFactory;

    protected $table = 'felt_reports';

    protected $fillable = [
        'user_id',
        'city_id',
        'earthquake_event_id',
        'intensity_levels',
    ];

    protected function casts(): array
    {
        return [
            'intensity_levels' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function earthquakeEvent(): BelongsTo
    {
        return $this->belongsTo(EarthquakeEvent::class, 'earthquake_event_id');
    }
}
