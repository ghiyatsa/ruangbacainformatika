<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropUnique('books_issn_unique');
            $table->unique(
                ['issn', 'edition', 'pages'],
                'books_issn_edition_pages_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropUnique('books_issn_edition_pages_unique');
            $table->unique('issn');
        });
    }
};
