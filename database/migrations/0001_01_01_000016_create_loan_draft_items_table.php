<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_draft_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loan_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['loan_draft_id', 'book_id'],
                'loan_draft_items_draft_book_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_draft_items');
    }
};
