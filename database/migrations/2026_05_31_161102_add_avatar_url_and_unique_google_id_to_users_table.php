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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('avatar_url')->nullable()->after('google_id');
        });

        $duplicateGoogleIds = DB::table('users')
            ->select('google_id')
            ->whereNotNull('google_id')
            ->groupBy('google_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('google_id');

        foreach ($duplicateGoogleIds as $googleId) {
            $duplicateUserIds = DB::table('users')
                ->where('google_id', $googleId)
                ->orderBy('id')
                ->pluck('id');

            $duplicateUserIds
                ->slice(1)
                ->each(fn (int $userId): int => DB::table('users')->where('id', $userId)->update([
                    'google_id' => null,
                    'updated_at' => now(),
                ]));
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['google_id']);
            $table->dropColumn('avatar_url');
        });
    }
};
