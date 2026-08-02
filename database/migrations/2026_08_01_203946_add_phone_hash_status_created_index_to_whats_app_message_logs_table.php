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
        Schema::table('whats_app_message_logs', function (Blueprint $table) {
            $table->index(['phone_number_hash', 'status', 'created_at'], 'walogs_phone_status_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_message_logs', function (Blueprint $table) {
            $table->dropIndex('walogs_phone_status_created_index');
        });
    }
};
