<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosk_idempotency_records', function (Blueprint $table): void {
            $table->id();

            // Kunci yang dikirim klien lewat header Idempotency-Key.
            $table->string('key', 191)->unique();

            // Sidik jari permintaan: endpoint + anggota + daftar buku. Dipakai
            // untuk memastikan key yang sama tidak dipakai ulang untuk muatan
            // yang berbeda (yang akan menyembunyikan bug klien).
            $table->string('fingerprint', 64);

            // Respons sukses yang sudah diputar ulang apa adanya.
            $table->unsignedSmallInteger('status_code');
            $table->json('response_body');

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_idempotency_records');
    }
};
