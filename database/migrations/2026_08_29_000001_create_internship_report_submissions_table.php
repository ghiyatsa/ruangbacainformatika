<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_reports', function (Blueprint $table): void {
            $table->string('file_path')->nullable()->after('keywords');
            $table->string('company_name')->nullable()->after('file_path');
            $table->string('academic_advisor')->nullable()->after('company_name');
            $table->string('field_advisor')->nullable()->after('academic_advisor');
        });

        Schema::create('internship_report_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internship_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('academic_advisor');
            $table->string('field_advisor')->nullable();
            $table->year('year');
            $table->text('abstract');
            $table->string('keywords')->nullable();
            $table->string('report_file_path');
            $table->string('endorsement_file_path')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('revision_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('receipt_number')->nullable()->unique();
            $table->string('receipt_token', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_report_submissions');

        Schema::table('internship_reports', function (Blueprint $table): void {
            $table->dropColumn([
                'file_path',
                'company_name',
                'academic_advisor',
                'field_advisor',
            ]);
        });
    }
};
