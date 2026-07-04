<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('similarity_sync_statuses', function (Blueprint $table): void {
            // Drop unique index on source_skripsi_id
            $table->dropUnique(['source_skripsi_id']);

            // Rename source_skripsi_id to syncable_id
            $table->renameColumn('source_skripsi_id', 'syncable_id');

            // Add syncable_type column (nullable initially)
            $table->string('syncable_type')->after('id')->nullable();
        });

        // Backfill existing rows with Skripsi model type
        DB::table('similarity_sync_statuses')->update([
            'syncable_type' => 'App\\Models\\Skripsi',
        ]);

        Schema::table('similarity_sync_statuses', function (Blueprint $table): void {
            // Make syncable_type non-nullable
            $table->string('syncable_type')->nullable(false)->change();

            // Add composite unique index on syncable_type and syncable_id
            $table->unique(['syncable_type', 'syncable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('similarity_sync_statuses', function (Blueprint $table): void {
            $table->dropUnique(['syncable_type', 'syncable_id']);
            $table->dropColumn('syncable_type');
            $table->renameColumn('syncable_id', 'source_skripsi_id');
            $table->unique('source_skripsi_id');
        });
    }
};
