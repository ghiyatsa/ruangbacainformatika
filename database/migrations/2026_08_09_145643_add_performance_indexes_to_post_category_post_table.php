<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_category_post', function (Blueprint $table) {
            // Existing unique index starts with post_category_id — reverse index for post->categories() lookup
            $table->index(['post_id', 'post_category_id'], 'pcp_post_id_category_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('post_category_post', function (Blueprint $table) {
            $table->dropIndex('pcp_post_id_category_id_index');
        });
    }
};
