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
        if (! $this->supportsFullTextIndexes()) {
            return;
        }

        Schema::table('skripsis', function (Blueprint $table): void {
            $table->fullText(
                ['title', 'author_name', 'abstract', 'keywords'],
                'skripsis_search_fulltext'
            );
        });

        Schema::table('theses', function (Blueprint $table): void {
            $table->fullText(
                ['title', 'author_name', 'abstract', 'keywords'],
                'theses_search_fulltext'
            );
        });

        Schema::table('internship_reports', function (Blueprint $table): void {
            $table->fullText(
                ['title', 'author_name', 'abstract', 'keywords'],
                'internship_reports_search_fulltext'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsFullTextIndexes()) {
            return;
        }

        Schema::table('internship_reports', function (Blueprint $table): void {
            $table->dropFullText('internship_reports_search_fulltext');
        });

        Schema::table('theses', function (Blueprint $table): void {
            $table->dropFullText('theses_search_fulltext');
        });

        Schema::table('skripsis', function (Blueprint $table): void {
            $table->dropFullText('skripsis_search_fulltext');
        });
    }

    protected function supportsFullTextIndexes(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
