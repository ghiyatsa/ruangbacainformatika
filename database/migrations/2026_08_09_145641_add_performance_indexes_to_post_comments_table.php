<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            // Used by BlogController::show() — WHERE post_id = ? AND parent_id IS NULL
            $table->index(['post_id', 'parent_id'], 'post_comments_post_parent_index');
        });
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropIndex('post_comments_post_parent_index');
        });
    }
};
