<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weather_readings', function (Blueprint $table) {
            $table->decimal('precipitation_mm', 6, 2)->nullable()->after('pressure_msl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_readings', function (Blueprint $table) {
            $table->dropColumn('precipitation_mm');
        });
    }
};
