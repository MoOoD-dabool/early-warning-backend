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
        Schema::create('earthquake_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 255)->unique();
            $table->decimal('magnitude', 8, 2);
            $table->decimal('depth_km', 8, 2);
            $table->string('location_name', 255);
            $table->foreignId('city_id')->nullable();
            $table->boolean('processed')->default(false);
            $table->dateTime('occurred_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earthquake_events');
    }
};