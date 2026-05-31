<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author_name');
            $table->string('student_id')->index();
            $table->year('year');
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('theses', function (Blueprint $table): void {
                $table->fullText(
                    ['title', 'author_name', 'abstract', 'keywords'],
                    'theses_search_fulltext',
                );
            });
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('theses', function (Blueprint $table): void {
                $table->dropFullText('theses_search_fulltext');
            });
        }

        Schema::dropIfExists('theses');
    }
};
