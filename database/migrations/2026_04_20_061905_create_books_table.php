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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->string('isbn', 20)->unique()->nullable();
            $table->string('issn', 20)->unique()->nullable();
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

            $table->index(['publisher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
