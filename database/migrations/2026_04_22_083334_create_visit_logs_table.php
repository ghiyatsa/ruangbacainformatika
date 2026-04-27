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
        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kiosk_device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('visitor_type');
            $table->string('identity_number')->nullable();
            $table->string('institution')->nullable();
            $table->string('phone')->nullable();
            $table->string('purpose');
            $table->text('notes')->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};
