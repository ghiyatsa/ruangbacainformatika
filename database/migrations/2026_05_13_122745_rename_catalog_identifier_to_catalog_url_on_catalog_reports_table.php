<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('catalog_reports')) {
            return;
        }

        if (Schema::hasColumn('catalog_reports', 'catalog_identifier') && ! Schema::hasColumn('catalog_reports', 'catalog_url')) {
            Schema::table('catalog_reports', function (Blueprint $table): void {
                $table->renameColumn('catalog_identifier', 'catalog_url');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('catalog_reports')) {
            return;
        }

        if (Schema::hasColumn('catalog_reports', 'catalog_url') && ! Schema::hasColumn('catalog_reports', 'catalog_identifier')) {
            Schema::table('catalog_reports', function (Blueprint $table): void {
                $table->renameColumn('catalog_url', 'catalog_identifier');
            });
        }
    }
};
