<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateHashes = DB::table('return_drafts')
            ->select('token_hash')
            ->whereNotNull('token_hash')
            ->groupBy('token_hash')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('token_hash');

        foreach ($duplicateHashes as $hash) {
            $keepId = DB::table('return_drafts')
                ->where('token_hash', $hash)
                ->max('id');

            DB::table('return_drafts')
                ->where('token_hash', $hash)
                ->where('id', '<', $keepId)
                ->delete();
        }

        Schema::table('return_drafts', function (Blueprint $table) {
            $table->unique('token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_drafts', function (Blueprint $table) {
            $table->dropUnique(['token_hash']);
        });
    }
};
