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
        Schema::table('books', function (Blueprint $table) {
            $table->string('issn', 20)
                ->nullable()
                ->unique()
                ->after('isbn');
            $table->boolean('is_borrowable')
                ->default(true)
                ->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['issn']);
            $table->dropColumn(['issn', 'is_borrowable']);
        });
    }
};
