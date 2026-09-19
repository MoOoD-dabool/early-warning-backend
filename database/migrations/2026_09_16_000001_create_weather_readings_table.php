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
        Schema::create('weather_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->decimal('temperature_c', 5, 2);
            $table->unsignedTinyInteger('humidity_percent');
            $table->decimal('wind_speed_kmh', 5, 2);
            $table->decimal('pressure_msl', 6, 2)->nullable();
            $table->unsignedSmallInteger('weather_code')->nullable();
            $table->dateTime('fetched_at');
            $table->timestamps();

            $table->index(['city_id', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_readings');
    }
};