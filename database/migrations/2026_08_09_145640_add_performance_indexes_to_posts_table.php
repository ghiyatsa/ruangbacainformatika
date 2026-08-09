<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Used by BlogQueryService::paginatePosts() — filters status, orders published_at DESC
            $table->index(['status', 'published_at'], 'posts_status_published_at_index');

            // Used by BlogQueryService::popularPosts() — filters status, orders view_count DESC
            $table->index(['status', 'view_count'], 'posts_status_view_count_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_published_at_index');
            $table->dropIndex('posts_status_view_count_index');
        });
    }
};
