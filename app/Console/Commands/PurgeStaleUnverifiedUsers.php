<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:purge-stale-unverified-users {--hours=72 : Purge local accounts that remain unverified past this many hours}')]
#[Description('Delete stale local accounts that never verified their email address')]
class PurgeStaleUnverifiedUsers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'));

        $staleUsers = User::query()
            ->where('auth_provider', 'local')
            ->whereNull('email_verified_at')
            ->where('created_at', '<=', $cutoff)
            ->whereDoesntHave('roles', function ($query): void {
                $query->whereIn('name', ['super_admin', 'staff']);
            });

        $deletedCount = (clone $staleUsers)->count();

        if ($deletedCount === 0) {
            $this->info('No stale unverified users found.');

            return self::SUCCESS;
        }

        $staleUsers->delete();

        $this->info("Purged {$deletedCount} stale unverified users.");

        return self::SUCCESS;
    }
}
