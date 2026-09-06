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
        Schema::dropIfExists('loan_draft_items');
        Schema::dropIfExists('loan_drafts');
        Schema::dropIfExists('return_draft_items');
        Schema::dropIfExists('return_drafts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('loan_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash')->nullable()->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('expires_at')->nullable();
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

        Schema::create('return_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash')->nullable()->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->json('selected_loan_item_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('return_draft_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('return_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
