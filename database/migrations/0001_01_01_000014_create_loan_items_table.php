<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_item_id')->constrained()->restrictOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'returned_at']);
            $table->index(['book_item_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
