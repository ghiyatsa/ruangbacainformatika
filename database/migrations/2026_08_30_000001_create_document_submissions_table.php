<?php

use App\Models\InternshipReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skripsis', function (Blueprint $table): void {
            $table->string('file_path')->nullable()->after('keywords');
            $table->string('academic_advisor')->nullable()->after('file_path');
        });

        Schema::create('document_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->index(); // 'internship_report', 'skripsi', 'book_donation'
            $table->nullableMorphs('submittable');
            $table->string('status', 20)->default('pending')->index(); // pending, revision, approved, rejected
            $table->string('title');

            // Kolom karya ilmiah (KP & Skripsi)
            $table->year('year')->nullable();
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->string('academic_advisor')->nullable();

            // Kolom khusus KP
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('field_advisor')->nullable();

            // Kolom khusus Buku Sumbangan
            $table->string('author_names')->nullable();
            $table->string('publisher_name')->nullable();
            $table->string('isbn')->nullable();
            $table->string('edition')->nullable();
            $table->string('pages')->nullable();
            $table->string('book_condition', 20)->nullable();

            // Berkas digital
            $table->string('document_file_path')->nullable();
            $table->string('endorsement_file_path')->nullable();

            // Review & Tanda Terima
            $table->text('revision_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('receipt_number')->nullable()->unique();
            $table->string('receipt_token', 64)->nullable()->unique();
            $table->timestamps();
        });

        // Migrasikan data lama jika tabel internship_report_submissions ada
        if (Schema::hasTable('internship_report_submissions')) {
            $oldSubmissions = DB::table('internship_report_submissions')->get();
            foreach ($oldSubmissions as $old) {
                DB::table('document_submissions')->insert([
                    'user_id' => $old->user_id,
                    'type' => 'internship_report',
                    'submittable_type' => $old->internship_report_id ? InternshipReport::class : null,
                    'submittable_id' => $old->internship_report_id,
                    'status' => $old->status,
                    'title' => $old->title,
                    'year' => $old->year,
                    'abstract' => $old->abstract,
                    'keywords' => $old->keywords,
                    'academic_advisor' => $old->academic_advisor,
                    'company_name' => $old->company_name,
                    'company_address' => $old->company_address,
                    'field_advisor' => $old->field_advisor,
                    'document_file_path' => $old->report_file_path,
                    'endorsement_file_path' => $old->endorsement_file_path,
                    'revision_notes' => $old->revision_notes,
                    'reviewed_by' => $old->reviewed_by,
                    'reviewed_at' => $old->reviewed_at,
                    'receipt_number' => $old->receipt_number,
                    'receipt_token' => $old->receipt_token,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_submissions');

        Schema::table('skripsis', function (Blueprint $table): void {
            $table->dropColumn(['file_path', 'academic_advisor']);
        });
    }
};
