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
        Schema::create('whats_app_message_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 50)->index();
            $table->string('notification_type')->nullable();
            $table->string('phone_number_hash', 64)->nullable()->index();
            $table->string('phone_number_masked', 30)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->string('provider_status')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->text('message_preview')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_message_logs');
    }
};
