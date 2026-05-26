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
        Schema::table('books', function (Blueprint $table): void {
            $table->index(
                ['is_published', 'is_featured', 'published_year', 'title'],
                'books_publish_feature_year_title_index'
            );
            $table->index(
                ['is_published', 'view_count', 'title'],
                'books_publish_view_title_index'
            );
        });

        Schema::table('loan_drafts', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'status', 'id'],
                'loan_drafts_user_status_id_index'
            );
            $table->index(
                ['user_id', 'status', 'expires_at'],
                'loan_drafts_user_status_expires_at_index'
            );
        });

        Schema::table('loan_draft_items', function (Blueprint $table): void {
            $table->unique(
                ['loan_draft_id', 'book_id'],
                'loan_draft_items_draft_book_unique'
            );
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at'],
                'notifications_notifiable_read_at_index'
            );
            $table->index(
                ['notifiable_type', 'notifiable_id', 'created_at'],
                'notifications_notifiable_created_at_index'
            );
        });

        Schema::table('skripsis', function (Blueprint $table): void {
            $table->index('student_id');
        });

        Schema::table('theses', function (Blueprint $table): void {
            $table->index('student_id');
        });

        Schema::table('internship_reports', function (Blueprint $table): void {
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_reports', function (Blueprint $table): void {
            $table->dropIndex(['student_id']);
        });

        Schema::table('theses', function (Blueprint $table): void {
            $table->dropIndex(['student_id']);
        });

        Schema::table('skripsis', function (Blueprint $table): void {
            $table->dropIndex(['student_id']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex('notifications_notifiable_created_at_index');
            $table->dropIndex('notifications_notifiable_read_at_index');
        });

        Schema::table('loan_draft_items', function (Blueprint $table): void {
            $table->dropUnique('loan_draft_items_draft_book_unique');
        });

        Schema::table('loan_drafts', function (Blueprint $table): void {
            $table->dropIndex('loan_drafts_user_status_expires_at_index');
            $table->dropIndex('loan_drafts_user_status_id_index');
        });

        Schema::table('books', function (Blueprint $table): void {
            $table->dropIndex('books_publish_view_title_index');
            $table->dropIndex('books_publish_feature_year_title_index');
        });
    }
};
