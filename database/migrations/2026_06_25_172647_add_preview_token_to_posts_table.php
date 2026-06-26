<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'preview_token')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->uuid('preview_token')->nullable()->unique()->after('slug');
            });
        }

        // Generate tokens for existing posts
        Post::query()->each(function ($post) {
            if (! $post->preview_token) {
                $post->updateQuietly([
                    'preview_token' => (string) Str::uuid(),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('posts', 'preview_token')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('preview_token');
            });
        }
    }
};
