<?php

namespace App\Console\Commands;

use App\Repositories\SettingRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Kelola API key bersama untuk aplikasi kiosk (Flutter).
 *
 * Key disimpan sebagai hash di settings (kiosk.api_key_hash); plaintext hanya
 * ditampilkan sekali saat dibuat/dirotasi. Menghapus key akan langsung mematikan
 * seluruh akses API kiosk tanpa perlu membangun ulang aplikasi.
 */
class KioskApiKeyCommand extends Command
{
    protected $signature = 'kiosk:api-key
        {action=show : show|generate|revoke}
        {--force : Lewati konfirmasi saat mengrotasi/mencabut}';

    protected $description = 'Kelola API key untuk aplikasi kiosk (Flutter)';

    public function handle(SettingRepository $settingRepository): int
    {
        return match ($this->argument('action')) {
            'show' => $this->showStatus($settingRepository),
            'generate' => $this->generate($settingRepository),
            'revoke' => $this->revoke($settingRepository),
            default => $this->invalidAction(),
        };
    }

    protected function showStatus(SettingRepository $settingRepository): int
    {
        $hash = $settingRepository->get('kiosk', 'api_key_hash');

        if (! is_string($hash) || $hash === '') {
            $this->warn('API key kiosk belum dibuat.');
            $this->line('Buat dengan: php artisan kiosk:api-key generate');

            return self::SUCCESS;
        }

        $this->info('API key kiosk sudah aktif.');
        $this->line('  Dibuat : '.($settingRepository->get('kiosk', 'api_key_created_at') ?? '-'));

        return self::SUCCESS;
    }

    protected function generate(SettingRepository $settingRepository): int
    {
        $existing = $settingRepository->get('kiosk', 'api_key_hash');

        if (is_string($existing) && $existing !== '' && ! $this->option('force')) {
            if (! $this->confirm('API key lama akan langsung berhenti berlaku. Lanjutkan?', false)) {
                $this->line('Dibatalkan.');

                return self::SUCCESS;
            }
        }

        $plainKey = 'rbk_'.Str::random(56);

        $settingRepository->put('kiosk', 'api_key_hash', Hash::make($plainKey));
        $settingRepository->put('kiosk', 'api_key_created_at', now()->toIso8601String());

        $this->info('API key kiosk berhasil dibuat.');
        $this->newLine();
        $this->line('  '.$plainKey);
        $this->newLine();
        $this->warn('Simpan key ini sekarang — tidak akan ditampilkan lagi.');
        $this->line('Masukkan ke konfigurasi aplikasi Flutter sebagai header:');
        $this->line('  X-Kiosk-Api-Key: <key>');

        return self::SUCCESS;
    }

    protected function revoke(SettingRepository $settingRepository): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Seluruh akses API kiosk akan langsung ditolak. Lanjutkan?', false)) {
                $this->line('Dibatalkan.');

                return self::SUCCESS;
            }
        }

        $settingRepository->forget('kiosk', 'api_key_hash');
        $settingRepository->forget('kiosk', 'api_key_created_at');

        $this->info('API key kiosk dicabut. Seluruh akses API kiosk kini ditolak.');

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->error('Aksi tidak dikenal. Gunakan: show, generate, atau revoke.');

        return self::FAILURE;
    }
}
