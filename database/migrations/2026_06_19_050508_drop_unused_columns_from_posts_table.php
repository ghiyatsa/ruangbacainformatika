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
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('posts', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('posts', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
            if (Schema::hasColumn('posts', 'seo_description')) {
                $table->dropColumn('seo_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('tags')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });
    }
};
