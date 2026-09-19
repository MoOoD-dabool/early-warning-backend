<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remembers, per device, whether the user left "alert sound" switched on
     * in the app's Permissions screen. When it's off, a siren alert is still
     * delivered as a push notification but with the phone's normal
     * notification sound instead of the siren. Defaults to true so every
     * already-registered device keeps today's behavior until its app
     * reports otherwise.
     */
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->boolean('sound_enabled')->default(true)->after('platform');
        });
    }

    public function down(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropColumn('sound_enabled');
        });
    }
};
