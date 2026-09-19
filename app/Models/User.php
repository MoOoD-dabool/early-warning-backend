<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'google_id',
        'city_id',
        'street_name',
        'building_number',
        'email',
        'email_verified_at',
        'password',
        'profile_image',
        'remember_token',
        'is_verified',
        'otp_code',
        'otp_expires_at',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'otp_expires_at' => 'datetime',
        ];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    public function userAlerts(): HasMany
    {
        return $this->hasMany(UserAlert::class, 'user_id');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class, 'user_id');
    }

    public function reliefRequests(): HasMany
    {
        return $this->hasMany(ReliefRequest::class, 'user_id');
    }

    public function feltReports(): HasMany
    {
        return $this->hasMany(FeltReport::class, 'user_id');
    }

    protected static function booted(): void
    {
        // device_tokens, relief_requests, and felt_reports cascade-delete at
        // the DB level already. user_alerts and reports don't have a DB
        // constraint: alerts are disposable (deleted outright), but reports
        // are a complaint/support record the admin panel keeps even after
        // the account is gone, so the link is cleared instead of the row
        // being destroyed. Sanctum tokens are already unusable the instant
        // the user row is gone (nothing left for them to resolve to) - this
        // just clears the now-dead rows instead of leaving them behind.
        static::deleting(function (User $user) {
            $user->userAlerts()->delete();
            $user->reports()->update(['user_id' => null]);
            $user->tokens()->delete();
        });
    }
}