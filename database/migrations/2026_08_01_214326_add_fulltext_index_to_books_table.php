<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('books', function (Blueprint $table) {
                $table->fullText(
                    ['title', 'subtitle', 'description', 'isbn', 'issn', 'ddc_code'],
                    'books_search_fulltext',
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropFullText('books_search_fulltext');
            });
        }
    }
};
