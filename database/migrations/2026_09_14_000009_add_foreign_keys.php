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
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('city_id')
                    ->references('id')
                    ->on('cities')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('earthquake_events', function (Blueprint $table) {
                $table->foreign('city_id')
                    ->references('id')
                    ->on('cities')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('alerts', function (Blueprint $table) {
                $table->foreign('disaster_type_id')
                    ->references('id')
                    ->on('disaster_types')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('alerts', function (Blueprint $table) {
                $table->foreign('city_id')
                    ->references('id')
                    ->on('cities')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('alerts', function (Blueprint $table) {
                $table->foreign('earthquake_event_id')
                    ->references('id')
                    ->on('earthquake_events')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('user_alerts', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('user_alerts', function (Blueprint $table) {
                $table->foreign('alert_id')
                    ->references('id')
                    ->on('alerts')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }

        try {
            Schema::table('reports', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            });
        } catch (\Throwable) {
            // Skip FK constraint if the driver does not support it or the table/column is unavailable
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['city_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('earthquake_events', function (Blueprint $table) {
                $table->dropForeign(['city_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('alerts', function (Blueprint $table) {
                $table->dropForeign(['disaster_type_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('alerts', function (Blueprint $table) {
                $table->dropForeign(['city_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('alerts', function (Blueprint $table) {
                $table->dropForeign(['earthquake_event_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('user_alerts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('user_alerts', function (Blueprint $table) {
                $table->dropForeign(['alert_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable) {}
    }
};
