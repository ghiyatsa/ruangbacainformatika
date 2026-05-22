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
        Schema::table('skripsis', function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->default(0)->after('keywords');
        });

        Schema::table('theses', function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->default(0)->after('keywords');
        });

        Schema::table('internship_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->default(0)->after('keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skripsis', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });

        Schema::table('theses', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });

        Schema::table('internship_reports', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
};
