<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DisasterType extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'disaster_types';

    protected $fillable = [
        'key',
        'name_ar',
        'name_en',
        'instructions_before_ar',
        'instructions_before_en',
        'instructions_during_ar',
        'instructions_during_en',
        'instructions_after_ar',
        'instructions_after_en',
        'audio_file_ar',
        'audio_file_en',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'disaster_type_id');
    }
}