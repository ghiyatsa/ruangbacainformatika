<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Services\ActivityLogService;
use App\Services\KioskApiKeyManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Kelola API key kiosk untuk aplikasi Flutter.
 *
 * Key disimpan sebagai hash (lihat KioskApiKeyManager), jadi plaintext hanya
 * bisa ditampilkan sekali — tepat setelah dibuat. Karena itu key baru
 * ditampilkan lewat notifikasi persisten yang bisa disalin, bukan lewat field
 * form yang bisa dibaca ulang kapan saja.
 */
class KioskApiKeySettings extends Page
{
    protected static ?string $navigationLabel = 'API Key Kiosk';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'API Key Kiosk';

    protected static ?string $slug = 'kiosk-api-key';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Key;

    protected string $view = 'filament.clusters.settings.pages.kiosk-api-key';

    /**
     * Hanya administrator (super_admin / staff) yang boleh mengelola
     * kredensial ini.
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->hasAdministrativeRole();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label(fn (): string => $this->manager()->isConfigured()
                    ? 'Rotasi API Key'
                    : 'Buat API Key')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => $this->manager()->isConfigured()
                    ? 'Rotasi API Key Kiosk'
                    : 'Buat API Key Kiosk')
                ->modalDescription(fn (): string => $this->manager()->isConfigured()
                    ? 'API key lama akan langsung berhenti berlaku. Semua aplikasi kiosk yang masih memakai key lama harus diperbarui.'
                    : 'API key baru akan dibuat. Salin dan simpan key ini — hanya ditampilkan sekali.')
                ->modalSubmitActionLabel('Ya, lanjutkan')
                ->action(function (): void {
                    $plainKey = $this->manager()->generate();

                    app(ActivityLogService::class)->log(
                        'settings.kiosk.api_key.generated',
                        'API key kiosk dibuat',
                        'Kredensial kiosk',
                        ['section' => KioskApiKeyManager::SECTION],
                    );

                    Notification::make()
                        ->success()
                        ->title('API key kiosk berhasil dibuat')
                        ->body("**{$plainKey}**\n\nSalin sekarang — key ini tidak akan ditampilkan lagi. "
                            .'Masukkan ke konfigurasi aplikasi Flutter (`config/kiosk.json`) pada bagian `apiKey`.')
                        ->persistent()
                        ->send();
                }),

            Action::make('revoke')
                ->label('Cabut API Key')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => $this->manager()->isConfigured())
                ->requiresConfirmation()
                ->modalHeading('Cabut API Key Kiosk')
                ->modalDescription('Seluruh akses API kiosk akan langsung ditolak. Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, cabut')
                ->action(function (): void {
                    $this->manager()->revoke();

                    app(ActivityLogService::class)->log(
                        'settings.kiosk.api_key.revoked',
                        'API key kiosk dicabut',
                        'Kredensial kiosk',
                        ['section' => KioskApiKeyManager::SECTION],
                    );

                    Notification::make()
                        ->success()
                        ->title('API key kiosk dicabut')
                        ->body('Seluruh akses API kiosk kini ditolak.')
                        ->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $manager = $this->manager();
        $configured = $manager->isConfigured();
        $preview = $manager->maskedPreview();
        $createdAt = $manager->createdAt();

        return $schema
            ->components([
                Section::make('Status Kredensial')
                    ->description('API key ini dipakai aplikasi kiosk (Flutter) untuk mengakses endpoint `/api/kiosk/*`.')
                    ->schema([
                        Text::make($configured ? 'AKTIF' : 'BELUM DIBUAT')
                            ->badge()
                            ->color($configured ? 'success' : 'danger'),

                        Text::make($configured
                            ? 'API key kiosk sedang aktif.'
                            : 'API key kiosk belum dibuat. Aplikasi kiosk tidak dapat mengakses API.')
                            ->color('gray'),

                        Text::make('Pratinjau: '.($preview ?? '-'))
                            ->visible($configured)
                            ->color('gray')
                            ->size('sm'),

                        Text::make('Dibuat: '.($createdAt === null ? '-' : $this->formatDate($createdAt)))
                            ->visible($configured)
                            ->color('gray')
                            ->size('sm'),

                        Text::make('Untuk keamanan, key lengkap hanya ditampilkan sekali saat dibuat. '
                            .'Jika key hilang, gunakan tombol "Rotasi API Key" untuk membuat yang baru.')
                            ->color('warning')
                            ->size('sm'),
                    ]),

                Section::make('Cara Memasang di Aplikasi Kiosk')
                    ->description('Salin nilai berikut ke berkas `config/kiosk.json` di folder aplikasi kiosk.')
                    ->schema([
                        Text::make('baseUrl: '.url('/api/kiosk'))
                            ->badge()
                            ->color('gray')
                            ->copyable(),

                        Text::make('apiKey: <tempel API key di sini>')
                            ->badge()
                            ->color('gray')
                            ->size('sm'),
                    ]),
            ]);
    }

    protected function formatDate(string $iso): string
    {
        try {
            return Carbon::parse($iso)
                ->locale('id')
                ->translatedFormat('d F Y, H:i').' WIB';
        } catch (\Exception) {
            return $iso;
        }
    }

    protected function manager(): KioskApiKeyManager
    {
        return app(KioskApiKeyManager::class);
    }
}
