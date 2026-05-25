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
        Schema::create('skripsis', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author_name');
            $table->string('student_id');
            $table->year('year');
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });

        Schema::create('theses', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author_name');
            $table->string('student_id');
            $table->year('year');
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });

        Schema::create('internship_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author_name');
            $table->string('student_id');
            $table->year('year');
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });

        Schema::create('similarity_sync_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('source_skripsi_id')->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->string('last_operation', 20)->default('upsert');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('similarity_sync_statuses');
        Schema::dropIfExists('internship_reports');
        Schema::dropIfExists('theses');
        Schema::dropIfExists('skripsis');
    }
};
