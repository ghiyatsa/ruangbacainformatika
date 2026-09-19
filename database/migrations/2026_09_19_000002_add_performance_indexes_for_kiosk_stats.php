<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            // Dipakai KioskDashboardStatsService::getStatsForRequest() — hitung
            // peminjaman hari ini lewat rentang loans.borrowed_at.
            $table->index('borrowed_at', 'loans_borrowed_at_index');
        });

        Schema::table('loan_items', function (Blueprint $table): void {
            // Dipakai KioskDashboardStatsService::getStatsForRequest() — hitung
            // pengembalian hari ini lewat rentang loan_items.returned_at.
            // Indeks komposit (loan_id, returned_at) tidak bisa melayani rentang
            // pada returned_at saja karena loan_id adalah kolom depan.
            $table->index('returned_at', 'loan_items_returned_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropIndex('loans_borrowed_at_index');
        });

        Schema::table('loan_items', function (Blueprint $table): void {
            $table->dropIndex('loan_items_returned_at_index');
        });
    }
};
