<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * A single known admin account for LOCAL testing only. Its password is
     * written in this file, so it must never exist on a real server: outside
     * the `local` environment this seeder does nothing. Real admin accounts
     * are created deliberately (by hand, or from the panel by a super_admin)
     * with a password of the owner's own choosing.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        if (! Schema::hasTable('admin')) {
            return;
        }

        Admin::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Admin@12345'),
            ],
        );
    }
}