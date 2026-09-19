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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disaster_type_id');
            $table->foreignId('city_id');
            $table->foreignId('earthquake_event_id')->nullable();
            $table->string('severity', 255);
            $table->boolean('trigger_siren')->default(false);
            $table->text('message_ar');
            $table->text('message_en');
            $table->dateTime('issued_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};