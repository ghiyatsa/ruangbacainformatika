<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_histories', function (Blueprint $table) {
            // Replaces LIKE '%term%' (full scan) with MATCH AGAINST fulltext search
            $table->fullText('query', 'search_histories_query_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('search_histories', function (Blueprint $table) {
            $table->dropFullText('search_histories_query_fulltext');
        });
    }
};
