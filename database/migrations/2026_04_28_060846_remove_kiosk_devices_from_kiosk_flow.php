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
        if (Schema::hasColumn('visit_logs', 'kiosk_device_id')) {
            Schema::table('visit_logs', function (Blueprint $table) {
                $table->dropForeign(['kiosk_device_id']);
                $table->dropColumn('kiosk_device_id');
            });
        }

        if (Schema::hasColumn('loans', 'kiosk_device_id')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropForeign(['kiosk_device_id']);
                $table->dropColumn('kiosk_device_id');
            });
        }

        Schema::dropIfExists('kiosk_devices');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('kiosk_devices')) {
            Schema::create('kiosk_devices', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('kiosk_identifier')->unique();
                $table->string('registration_code', 12)->unique();
                $table->string('device_token_hash')->nullable();
                $table->string('status')->default('pending');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('visit_logs', 'kiosk_device_id')) {
            Schema::table('visit_logs', function (Blueprint $table) {
                $table->foreignId('kiosk_device_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('loans', 'kiosk_device_id')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->foreignId('kiosk_device_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }
};
