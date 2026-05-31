<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('issn', 20)->nullable()->unique();
            $table->string('ddc_code', 20)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('cover_image_editor_state')->nullable();
            $table->string('edition')->nullable();
            $table->year('published_year')->nullable();
            $table->integer('pages')->nullable();
            $table->string('language', 30)->default('Indonesia');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_borrowable')->default(true);
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->foreignId('publisher_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->index(
                ['is_published', 'is_featured', 'published_year', 'title'],
                'books_publish_feature_year_title_index',
            );
            $table->index(
                ['is_published', 'view_count', 'title'],
                'books_publish_view_title_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
