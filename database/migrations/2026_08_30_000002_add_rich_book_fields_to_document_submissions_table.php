<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_submissions', function (Blueprint $table): void {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('slug')->nullable()->after('subtitle');
            $table->text('description')->nullable()->after('slug');
            $table->string('issn', 20)->nullable()->after('isbn');
            $table->string('ddc_code', 20)->nullable()->after('issn');
            $table->string('language', 30)->nullable()->default('Indonesia')->after('ddc_code');
            $table->foreignId('publisher_id')->nullable()->after('publisher_name')->constrained('publishers')->nullOnDelete();
            $table->json('author_ids')->nullable()->after('author_names');
            $table->json('category_ids')->nullable()->after('author_ids');
            $table->string('cover_image')->nullable()->after('book_condition');
            $table->integer('copies_count')->default(1)->after('pages');
            $table->string('submission_batch_token', 64)->nullable()->index()->after('receipt_token');
        });
    }

    public function down(): void
    {
        Schema::table('document_submissions', function (Blueprint $table): void {
            $table->dropForeign(['publisher_id']);
            $table->dropColumn([
                'subtitle',
                'slug',
                'description',
                'issn',
                'ddc_code',
                'language',
                'publisher_id',
                'author_ids',
                'category_ids',
                'cover_image',
                'copies_count',
                'submission_batch_token',
            ]);
        });
    }
};
