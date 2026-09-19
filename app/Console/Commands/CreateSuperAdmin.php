<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Creates the first super_admin account on a fresh server, where there is no
 * seeded admin (AdminSeeder only runs locally on purpose).
 *
 * The container's start script calls this automatically when ADMIN_EMAIL and
 * ADMIN_PASSWORD are set, so the whole setup can be done from the hosting
 * dashboard. It never overwrites an existing account, so it is safe for those
 * variables to still be present on later restarts.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create-super
        {--email= : Login email (default: the ADMIN_EMAIL environment variable)}
        {--name= : Display name (default: ADMIN_NAME, then "Admin")}
        {--password= : Password (default: ADMIN_PASSWORD; asked for when missing and running interactively)}
        {--reset-password : If the account already exists, set the given password on it instead of leaving it alone}';

    protected $description = 'Create a super_admin account for the admin panel (never overwrites an existing account unless --reset-password)';

    private const MIN_PASSWORD_LENGTH = 12;

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->fromEnvironment('ADMIN_EMAIL');
        $name = $this->option('name') ?: $this->fromEnvironment('ADMIN_NAME') ?: 'Admin';
        $password = $this->option('password') ?: $this->fromEnvironment('ADMIN_PASSWORD');

        if (! $email) {
            $this->error('No email given: pass --email or set the ADMIN_EMAIL environment variable.');

            return self::FAILURE;
        }

        $existing = Admin::query()->where('email', $email)->first();

        if ($existing && ! $this->option('reset-password')) {
            $this->info("An admin with this email already exists ({$existing->role}); nothing was changed.");

            return self::SUCCESS;
        }

        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Password (at least '.self::MIN_PASSWORD_LENGTH.' characters)');
        }

        if (! $password) {
            $this->error('No password given: pass --password or set the ADMIN_PASSWORD environment variable.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email:rfc', 'max:255'],
                'password' => ['required', 'string', 'min:'.self::MIN_PASSWORD_LENGTH],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if ($existing) {
            // The 'hashed' cast on Admin::$password hashes this on save.
            $existing->password = $password;
            $existing->save();
            $this->info("Password reset for {$email}.");

            return self::SUCCESS;
        }

        Admin::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => Admin::ROLE_SUPER_ADMIN,
        ]);

        $this->info("Super admin created: {$email}. Two-factor setup is required at the first login.");

        return self::SUCCESS;
    }

    /**
     * Read straight from the process environment: the container caches the
     * config at start, and env() is only reliable inside config files.
     */
    private function fromEnvironment(string $key): ?string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            $value = $_SERVER[$key] ?? $_ENV[$key] ?? null;
        }

        return filled($value) ? (string) $value : null;
    }
}
