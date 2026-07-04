<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Repositories\SettingRepository;
use App\Services\ActivityLogService;
use App\Services\SimilarityFullSyncDispatcher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Throwable;

class IntegrationSettings extends Page
{
    protected static ?string $navigationLabel = 'Integrasi';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Pengaturan Integrasi';

    protected static ?string $slug = 'integrasi';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ServerStack;

    protected string $view = 'filament.clusters.settings.pages.general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $values = $this->settingRepository()->sectionValues('integration', $this->defaultValues());

        $values = $this->decryptSecretFields($values);

        $this->form->fill($values);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                Section::make('Cloudflare Turnstile')
                    ->description('Verifikasi tambahan untuk formulir publik.')
                    ->schema([
                        Toggle::make('turnstile_enabled')
                            ->label('Aktifkan Turnstile')
                            ->helperText('Gunakan untuk perlindungan tambahan pada formulir publik.')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),
                Section::make('API Kemiripan Skripsi')
                    ->description('Pengaturan layanan pemeriksaan kemiripan karya ilmiah.')
                    ->schema([
                        TextInput::make('similarity_api_url')
                            ->label('URL Endpoint API')
                            ->url()
                            ->required()
                            ->placeholder('http://localhost:8181'),
                        TextInput::make('similarity_api_secret')
                            ->label('Secret Token / API Key')
                            ->autocomplete('off')
                            ->required()
                            ->suffixAction(
                                Action::make('generateSecret')
                                    ->icon('heroicon-m-key')
                                    ->color('warning')
                                    ->tooltip('Buat token baru')
                                    ->requiresConfirmation()
                                    ->modalHeading('Buat Token API Baru')
                                    ->modalDescription('Token lama akan diganti setelah pengaturan disimpan.')
                                    ->modalSubmitActionLabel('Buat Token')
                                    ->action(function (Set $set) {
                                        $secret = Str::random(32);
                                        $set('similarity_api_secret', $secret);

                                        Notification::make()
                                            ->success()
                                            ->title('Token baru siap digunakan')
                                            ->body("Token baru: **{$secret}**")
                                            ->persistent()
                                            ->send();
                                    })
                            ),
                        TextInput::make('similarity_api_timeout')
                            ->label('Timeout (Detik)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(60)
                            ->default(10),
                        TextInput::make('similarity_api_top_k')
                            ->label('Top K (Jumlah Hasil)')
                            ->helperText('Jumlah hasil yang ditampilkan.')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(5),
                        TextInput::make('similarity_api_threshold')
                            ->label('Threshold (Ambang Batas)')
                            ->helperText('Nilai lebih tinggi akan memperketat hasil.')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->default(0.5),
                        TextInput::make('similarity_weight_judul')
                            ->label('Bobot Judul')
                            ->helperText('Total bobot disarankan 1.00.')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->default(0.7),
                        TextInput::make('similarity_weight_abstrak')
                            ->label('Bobot Abstrak')
                            ->helperText('Jika diubah, lakukan sinkron ulang data similarity.')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->default(0.2),
                        TextInput::make('similarity_weight_kata_kunci')
                            ->label('Bobot Kata Kunci')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->default(0.1),
                    ])
                    ->columns(2),

                Section::make('Notifikasi WhatsApp')
                    ->description('Pengaturan WhatsApp untuk notifikasi rutin.')
                    ->schema([
                        TextInput::make('whatsapp_api_url')
                            ->label('URL Endpoint WhatsApp API')
                            ->url()
                            ->placeholder('https://api.fonnte.com/send'),
                        TextInput::make('whatsapp_api_token')
                            ->label('API Token')
                            ->autocomplete('off'),
                        TextInput::make('whatsapp_failure_pause_threshold')
                            ->label('Batas Gagal Sebelum Jeda')
                            ->helperText('Isi 0 jika jeda tidak diperlukan.')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(5),
                        TextInput::make('whatsapp_failure_pause_window_minutes')
                            ->label('Window Gagal (Menit)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(1440)
                            ->default(15),
                    ])
                    ->columns(2),
            ])
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('resyncAllSkripsi')
                            ->label('Sinkronkan Ulang Semua Skripsi')
                            ->icon(Heroicon::OutlinedArrowPath)
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Sinkronkan Ulang Semua Skripsi')
                            ->modalDescription('Gunakan setelah bobot similarity berubah agar seluruh data diperbarui ulang. Proses tetap berjalan di antrean.')
                            ->modalSubmitActionLabel('Mulai Sinkron Ulang')
                            ->action(function (): void {
                                $result = app(SimilarityFullSyncDispatcher::class)->dispatch(
                                    chunk: 10,
                                    forceSync: false,
                                    initiatedByUserId: auth()->id(),
                                );

                                try {
                                    app(ActivityLogService::class)->log(
                                        'integration.skripsi_resync.triggered',
                                        'Sinkron ulang semua skripsi dipicu',
                                        'Integrasi',
                                        $result,
                                    );
                                } catch (Throwable) {
                                }

                                Notification::make()
                                    ->{$result['success'] ? 'success' : 'danger'}()
                                    ->title($result['success']
                                        ? ($result['mode'] === 'sync' ? 'Sinkron ulang selesai' : 'Sinkron ulang dimulai')
                                        : 'Sinkron ulang belum berhasil')
                                    ->body($result['success']
                                        ? ($result['mode'] === 'sync'
                                            ? 'Seluruh data similarity telah diperbarui.'
                                            : 'Proses sedang berjalan di antrean. Pastikan worker queue tetap aktif.')
                                        : ($result['error_message'] ?? 'Periksa koneksi Similarity API lalu coba lagi.'))
                                    ->persistent($result['mode'] === 'queued')
                                    ->send();
                            }),
                        Action::make('save')
                            ->label('Simpan')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $weightsChanged = $this->weightsHaveChanged($data);
        $existingValues = $this->decryptSecretFields(
            $this->settingRepository()->sectionValues('integration', $this->defaultValues()),
        );

        if (((float) ($data['similarity_weight_judul'] ?? 0)
            + (float) ($data['similarity_weight_abstrak'] ?? 0)
            + (float) ($data['similarity_weight_kata_kunci'] ?? 0)) <= 0) {
            Notification::make()
                ->danger()
                ->title('Bobot tidak valid')
                ->body('Total bobot harus lebih dari 0.')
                ->send();

            return;
        }

        // Encrypt secret fields before persisting
        $similaritySecret = filled($data['similarity_api_secret'] ?? null)
            ? encrypt($data['similarity_api_secret'])
            : null;
        $whatsAppToken = filled($data['whatsapp_api_token'] ?? null)
            ? encrypt($data['whatsapp_api_token'])
            : null;

        $savedValues = [
            'similarity_api_url' => $data['similarity_api_url'] ?? null,
            'similarity_api_secret' => $similaritySecret,
            'similarity_api_timeout' => $data['similarity_api_timeout'] ?? 10,
            'whatsapp_api_url' => $data['whatsapp_api_url'] ?? null,
            'whatsapp_api_token' => $whatsAppToken,
            'whatsapp_failure_pause_threshold' => $data['whatsapp_failure_pause_threshold'] ?? 5,
            'whatsapp_failure_pause_window_minutes' => $data['whatsapp_failure_pause_window_minutes'] ?? 15,
            'turnstile_enabled' => $data['turnstile_enabled'] ?? false,
            'similarity_api_top_k' => $data['similarity_api_top_k'] ?? 5,
            'similarity_api_threshold' => $data['similarity_api_threshold'] ?? 0.5,
            'similarity_weight_judul' => $data['similarity_weight_judul'] ?? 0.7,
            'similarity_weight_abstrak' => $data['similarity_weight_abstrak'] ?? 0.2,
            'similarity_weight_kata_kunci' => $data['similarity_weight_kata_kunci'] ?? 0.1,
        ];

        $this->settingRepository()->putMany('integration', $savedValues);
        cache()->forget('settings.integration.turnstile_enabled');

        app(ActivityLogService::class)->logSettingsUpdate(
            'integration',
            'Pengaturan integrasi',
            $existingValues,
            [
                ...$savedValues,
                'similarity_api_secret' => $data['similarity_api_secret'] ?? null,
                'whatsapp_api_token' => $data['whatsapp_api_token'] ?? null,
            ],
            ['similarity_api_secret', 'whatsapp_api_token'],
            ['weights_changed' => $weightsChanged],
        );

        Notification::make()
            ->success()
            ->title('Pengaturan integrasi disimpan')
            ->send();

        if ($weightsChanged) {
            Notification::make()
                ->warning()
                ->title('Bobot berubah')
                ->body('Sinkronkan ulang data similarity agar hasil tetap selaras.')
                ->persistent()
                ->send();
        }

        // Re-fill form with decrypted value so UI stays consistent
        $values = $this->decryptSecretFields(
            $this->settingRepository()->sectionValues('integration', $this->defaultValues()),
        );

        $this->form->fill($values);
    }

    /**
     * @return array<string, string|int|null>
     */
    protected function defaultValues(): array
    {
        return [
            'similarity_api_url' => (string) config('services.similarity_api.url', 'http://localhost:8181'),
            'similarity_api_secret' => (string) config('services.similarity_api.secret', ''),
            'similarity_api_timeout' => (int) config('services.similarity_api.timeout', 10),
            'whatsapp_api_url' => (string) config('services.fonnte.url', ''),
            'whatsapp_api_token' => (string) config('services.fonnte.token', ''),
            'whatsapp_failure_pause_threshold' => (int) config('services.fonnte.failure_pause_threshold', 5),
            'whatsapp_failure_pause_window_minutes' => (int) config('services.fonnte.failure_pause_window_minutes', 15),
            'turnstile_enabled' => false,
            'similarity_api_top_k' => 5,
            'similarity_api_threshold' => 0.5,
            'similarity_weight_judul' => 0.7,
            'similarity_weight_abstrak' => 0.2,
            'similarity_weight_kata_kunci' => 0.1,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function weightsHaveChanged(array $data): bool
    {
        $defaults = $this->defaultValues();
        $stored = $this->settingRepository()->sectionValues('integration', $defaults);

        return abs((float) ($stored['similarity_weight_judul'] ?? $defaults['similarity_weight_judul']) - (float) ($data['similarity_weight_judul'] ?? $defaults['similarity_weight_judul'])) > 0.0001
            || abs((float) ($stored['similarity_weight_abstrak'] ?? $defaults['similarity_weight_abstrak']) - (float) ($data['similarity_weight_abstrak'] ?? $defaults['similarity_weight_abstrak'])) > 0.0001
            || abs((float) ($stored['similarity_weight_kata_kunci'] ?? $defaults['similarity_weight_kata_kunci']) - (float) ($data['similarity_weight_kata_kunci'] ?? $defaults['similarity_weight_kata_kunci'])) > 0.0001;
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function decryptSecretFields(array $values): array
    {
        foreach (['similarity_api_secret', 'whatsapp_api_token'] as $key) {
            if (! filled($values[$key] ?? null)) {
                continue;
            }

            try {
                $values[$key] = decrypt($values[$key]);
            } catch (\Exception) {
            }
        }

        return $values;
    }
}
