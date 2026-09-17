<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom untuk permintaan hapus akun dengan masa tenggang.
 *
 * Alur:
 * 1. Anggota mengajukan penghapusan -> data pribadi langsung dianonimkan dan
 *    akun ditandai deleted_at. Riwayat peminjaman TIDAK disentuh.
 * 2. Selama masa tenggang, pemilik akun lama masih dapat membatalkan.
 * 3. Setelah tenggang lewat, baris user boleh dihapus permanen oleh perintah
 *    terjadwal, dan pada saat itu riwayat sudah aman karena foreign key
 *    memakai restrictOnDelete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->softDeletes();
            $table->timestamp('deletion_requested_at')->nullable()->after('deleted_at');
            $table->string('deletion_reason')->nullable()->after('deletion_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['deleted_at', 'deletion_requested_at', 'deletion_reason']);
        });
    }
};
