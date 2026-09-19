<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAlert extends Model
{
    use HasFactory;

    protected $table = 'user_alerts';

    protected $fillable = [
        'user_id',
        'alert_id',
        'received_at',
    ];


    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class, 'alert_id');
    }
}
