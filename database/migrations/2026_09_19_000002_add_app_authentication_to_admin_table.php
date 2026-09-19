<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Storage for the admin panel's two-factor login (authenticator-app
     * codes). Both columns hold values encrypted with the app's APP_KEY by the
     * model itself, which is why they're `text`: the ciphertext is much longer
     * than the secret it protects. Nullable = two-factor not set up yet.
     */
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};
