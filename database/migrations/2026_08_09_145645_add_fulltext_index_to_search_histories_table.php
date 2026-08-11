<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('search_histories', function (Blueprint $table) {
                // Replaces LIKE '%term%' (full scan) with MATCH AGAINST fulltext search
                $table->fullText('query', 'search_histories_query_fulltext');
            });
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('search_histories', function (Blueprint $table) {
                $table->dropFullText('search_histories_query_fulltext');
            });
        }
    }
};
