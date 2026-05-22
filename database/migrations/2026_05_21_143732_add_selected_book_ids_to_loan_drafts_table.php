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
        Schema::table('loan_drafts', function (Blueprint $table) {
            $table->json('selected_book_ids')
                ->nullable()
                ->after('consumed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_drafts', function (Blueprint $table) {
            $table->dropColumn('selected_book_ids');
        });
    }
};
