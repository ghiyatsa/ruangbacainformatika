<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\KioskApiKeyManager;
use Illuminate\Console\Command;

/**
 * Kelola API key bersama untuk aplikasi kiosk (Flutter).
 *
 * Logika generate/revoke didelegasikan ke KioskApiKeyManager agar identik
 * dengan halaman pengaturan pada panel admin (Filament) — tidak ada dua
 * implementasi yang bisa saling menyimpang.
 *
 * Key disimpan sebagai hash di settings (kiosk.api_key_hash); plaintext hanya
 * ditampilkan sekali saat dibuat/dirotasi. Mencabut key akan langsung mematikan
 * seluruh akses API kiosk tanpa perlu membangun ulang aplikasi.
 */
class KioskApiKeyCommand extends Command
{
    protected $signature = 'kiosk:api-key
        {action=show : show|generate|revoke}
        {--force : Lewati konfirmasi saat mengrotasi/mencabut}';

    protected $description = 'Kelola API key untuk aplikasi kiosk (Flutter)';

    public function handle(KioskApiKeyManager $manager): int
    {
        return match ($this->argument('action')) {
            'show' => $this->showStatus($manager),
            'generate' => $this->generate($manager),
            'revoke' => $this->revoke($manager),
            default => $this->invalidAction(),
        };
    }

    protected function showStatus(KioskApiKeyManager $manager): int
    {
        if (! $manager->isConfigured()) {
            $this->warn('API key kiosk belum dibuat.');
            $this->line('Buat dengan: php artisan kiosk:api-key generate');

            return self::SUCCESS;
        }

        $this->info('API key kiosk sudah aktif.');
        $this->line('  Dibuat : '.($manager->createdAt() ?? '-'));

        return self::SUCCESS;
    }

    protected function generate(KioskApiKeyManager $manager): int
    {
        if ($manager->isConfigured() && ! $this->option('force')) {
            if (! $this->confirm('API key lama akan langsung berhenti berlaku. Lanjutkan?', false)) {
                $this->line('Dibatalkan.');

                return self::SUCCESS;
            }
        }

        $plainKey = $manager->generate();

        $this->info('API key kiosk berhasil dibuat.');
        $this->newLine();
        $this->line('  '.$plainKey);
        $this->newLine();
        $this->warn('Simpan key ini sekarang — tidak akan ditampilkan lagi.');
        $this->line('Masukkan ke konfigurasi aplikasi Flutter sebagai header:');
        $this->line('  X-Kiosk-Api-Key: <key di atas>');

        return self::SUCCESS;
    }

    protected function revoke(KioskApiKeyManager $manager): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Seluruh akses API kiosk akan langsung ditolak. Lanjutkan?', false)) {
                $this->line('Dibatalkan.');

                return self::SUCCESS;
            }
        }

        $manager->revoke();

        $this->info('API key kiosk dicabut. Seluruh akses API kiosk kini ditolak.');

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->error('Aksi tidak dikenal. Gunakan: show, generate, atau revoke.');

        return self::FAILURE;
    }
}
