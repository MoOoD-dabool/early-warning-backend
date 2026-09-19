<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a two-tier admin hierarchy: 'super_admin' can manage other admin
 * accounts and view the activity log; plain 'admin' cannot. Every admin
 * account lives in this same table regardless of which door they log in
 * through (the existing Sanctum API login or the new Filament panel) —
 * the role is a property of the account, not of the login method.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
