<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Extends Authenticatable (not plain Model) so this same model/table can
 * log in two different ways: the existing Sanctum API token login
 * (AdminAuthController, used by nothing with a UI yet) and the Filament
 * panel's session-based login form. Both check the same `admin` rows, so
 * `role` applies no matter which door an admin logs in through.
 */
class Admin extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    use HasFactory, HasApiTokens, LogsActivity;
    // Two-factor login for the Filament panel (authenticator app + recovery
    // codes). The secret and codes are stored encrypted with APP_KEY.
    use InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';

    protected $table = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Both roles can get into the panel — the role only controls what
        // they can see/do once inside (see the Filament resources).
        return true;
    }

    // Never logs the password — only name/email/role changes (e.g. a
    // super_admin creating/editing another admin account).
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected $hidden = [
        'password',
    ];
}
