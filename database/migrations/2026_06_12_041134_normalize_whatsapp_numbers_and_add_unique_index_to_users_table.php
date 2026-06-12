<?php

use App\Support\WhatsAppPhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $phoneNumber = new WhatsAppPhoneNumber;

        $users = DB::table('users')
            ->select('id', 'whatsapp')
            ->whereNotNull('whatsapp')
            ->where('whatsapp', '!=', '')
            ->orderBy('id')
            ->get()
            ->map(function (object $user) use ($phoneNumber): object {
                $user->normalized_whatsapp = $phoneNumber->normalize($user->whatsapp);

                return $user;
            });

        $users
            ->filter(fn (object $user): bool => $user->normalized_whatsapp !== $user->whatsapp)
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'whatsapp' => $user->normalized_whatsapp,
                        'updated_at' => now(),
                    ]);
            });

        $users
            ->filter(fn (object $user): bool => $user->normalized_whatsapp !== null)
            ->groupBy('normalized_whatsapp')
            ->filter(fn (Collection $duplicates): bool => $duplicates->count() > 1)
            ->each(function (Collection $duplicates): void {
                $duplicates
                    ->slice(1)
                    ->each(function (object $user): void {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update([
                                'whatsapp' => null,
                                'whatsapp_verified_at' => null,
                                'updated_at' => now(),
                            ]);
                    });
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['whatsapp']);
        });
    }
};
