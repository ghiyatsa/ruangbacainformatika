<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('token_hash', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('consumed_at')->nullable();
            $table->json('selected_book_ids')->nullable();
            $table->timestamps();

            $table->index(
                ['user_id', 'status', 'id'],
                'loan_drafts_user_status_id_index',
            );
            $table->index(
                ['user_id', 'status', 'expires_at'],
                'loan_drafts_user_status_expires_at_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_drafts');
    }
};
