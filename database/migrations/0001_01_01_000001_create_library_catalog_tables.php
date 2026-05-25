<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('publishers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

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
        });

        Schema::create('book_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('internal_code')->unique();
            $table->string('shelf_location')->nullable();
            $table->enum('condition', ['good', 'damaged', 'lost'])->default('good');
            $table->enum('status', ['available', 'borrowed', 'maintenance', 'reserved'])->default('available')->index();
            $table->date('acquired_date')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('author_book', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['author_id', 'book_id']);
        });

        Schema::create('book_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['book_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('author_book');
        Schema::dropIfExists('book_items');
        Schema::dropIfExists('books');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('categories');
    }
};
