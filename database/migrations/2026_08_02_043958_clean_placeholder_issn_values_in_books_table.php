<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bersihkan nilai placeholder ISSN (mis. "-") dari data lama yang membuat
        // validasi ISSN gagal dan mengunci field ISBN di form admin.
        DB::table('books')
            ->where(function ($query) {
                $query->where('issn', '-')->orWhere('issn', '');
            })
            ->update(['issn' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada operasi balik yang aman untuk pembersihan data.
    }
};
