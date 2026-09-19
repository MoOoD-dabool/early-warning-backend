<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supports "Sign in with Google": a first-time Google sign-in creates the
 * user immediately (Google already verified the email, so no OTP step),
 * but Google never gives us a Syrian governorate/street/building number or
 * a local password — those get collected afterward on a "complete your
 * profile" screen. So all four have to become optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 255)->nullable()->unique()->after('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 255)->nullable()->change();
            $table->foreignId('city_id')->nullable()->change();
            $table->string('street_name', 255)->nullable()->change();
            $table->string('building_number', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn('google_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 255)->nullable(false)->change();
            $table->foreignId('city_id')->nullable(false)->change();
            $table->string('street_name', 255)->nullable(false)->change();
            $table->string('building_number', 255)->nullable(false)->change();
        });
    }
};
