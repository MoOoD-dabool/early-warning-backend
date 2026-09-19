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
        Schema::create('disaster_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()->comment('Stable machine identifier, e.g. earthquake, flood');
            $table->string('name_ar', 255);
            $table->string('name_en', 255);
            $table->text('instructions_before_ar');
            $table->text('instructions_before_en');
            $table->text('instructions_during_ar');
            $table->text('instructions_during_en');
            $table->text('instructions_after_ar');
            $table->text('instructions_after_en');
            $table->string('audio_file_ar', 255)->nullable();
            $table->string('audio_file_en', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disaster_types');
    }
};