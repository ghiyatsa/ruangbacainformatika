<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documentation_category_id')
                ->constrained('documentation_categories')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 255)->nullable();
            $table->longText('content');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['documentation_category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
        Schema::dropIfExists('documentation_categories');
    }
};
