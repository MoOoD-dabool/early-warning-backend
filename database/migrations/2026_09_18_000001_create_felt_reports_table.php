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
        Schema::create('felt_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('earthquake_event_id')->constrained()->cascadeOnDelete();
            $table->json('intensity_levels');
            $table->timestamps();

            // One report per user per earthquake — a second submission for
            // the same event is rejected rather than creating a duplicate.
            $table->unique(['user_id', 'earthquake_event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('felt_reports');
    }
};
