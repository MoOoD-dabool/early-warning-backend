<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('relief_teams', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('building_number');
            $table->string('relief_type', 30)->nullable()->after('status');
            $table->foreignId('disaster_type_id')
                ->nullable()
                ->after('relief_type')
                ->constrained()
                ->nullOnDelete();
            $table->timestamp('deployed_at')->nullable()->after('disaster_type_id');
        });

        // Backfill the new status column from the boolean it replaces, then
        // drop that boolean — matches this project's existing convention of
        // repurposing/migrating a column in place rather than running two
        // parallel fields (see the hurricane -> severe_storm migration).
        DB::table('relief_teams')->where('is_active', true)->update(['status' => 'active']);
        DB::table('relief_teams')->where('is_active', false)->update(['status' => 'inactive']);

        Schema::table('relief_teams', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relief_teams', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        DB::table('relief_teams')->where('status', 'active')->update(['is_active' => true]);
        DB::table('relief_teams')->where('status', '!=', 'active')->update(['is_active' => false]);

        Schema::table('relief_teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disaster_type_id');
            $table->dropColumn(['status', 'relief_type', 'deployed_at']);
        });
    }
};
