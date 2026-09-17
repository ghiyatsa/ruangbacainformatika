<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah foreign key loans.user_id dari cascadeOnDelete menjadi restrictOnDelete.
 *
 * Sebelumnya, menghapus satu baris users otomatis menghapus seluruh riwayat
 * peminjaman orang tersebut di level database. Riwayat sirkulasi adalah data
 * perpustakaan yang harus tetap utuh, termasuk ketika akun anggota dihapus
 * atas permintaan pemiliknya.
 *
 * Dengan restrictOnDelete, database menolak penghapusan user yang masih
 * memiliki pinjaman. Penghapusan akun dilakukan dengan anonimisasi data
 * pribadi, bukan dengan menghapus barisnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
