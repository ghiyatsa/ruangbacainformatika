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
        Schema::table('post_comments', function (Blueprint $table) {
            $table->foreignId('reply_to_comment_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('post_comments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_comment_id');
        });
    }
};
