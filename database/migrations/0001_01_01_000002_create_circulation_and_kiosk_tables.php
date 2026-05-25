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
        Schema::create('kiosk_devices', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('session_id')->unique();
            $table->string('device_token', 64)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('network_scope')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_logs', function (Blueprint $table): void {
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

        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('borrowed');
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_at']);
        });

        Schema::create('loan_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_item_id')->constrained()->restrictOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'returned_at']);
            $table->index(['book_item_id', 'returned_at']);
        });

        Schema::create('loan_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('token_hash', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('consumed_at')->nullable();
            $table->json('selected_book_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_draft_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loan_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('member_registration_claims', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('whatsapp', 30);
            $table->text('address');
            $table->string('token_hash', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('claimed_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_registration_claims');
        Schema::dropIfExists('loan_draft_items');
        Schema::dropIfExists('loan_drafts');
        Schema::dropIfExists('loan_items');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('visit_logs');
        Schema::dropIfExists('kiosk_devices');
    }
};
