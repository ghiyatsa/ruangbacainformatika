<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_registration_claims', function (Blueprint $table): void {
            // Menautkan claim ke perangkat kiosk pembuatnya, sehingga endpoint API
            // dapat menemukan claim aktif tanpa bergantung pada session web.
            $table->foreignId('kiosk_device_id')
                ->nullable()
                ->after('user_id')
                ->constrained('kiosk_devices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('member_registration_claims', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('kiosk_device_id');
        });
    }
};
