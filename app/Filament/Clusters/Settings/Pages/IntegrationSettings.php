<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Repositories\SettingRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class IntegrationSettings extends Page
{
    protected static ?string $navigationLabel = 'Integrasi API';

    protected static ?string $title = 'Pengaturan Integrasi API';

    protected static ?string $slug = 'integrasi';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ServerStack;

    protected string $view = 'filament.clusters.settings.pages.general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $values = $this->settingRepository()->sectionValues('integration', $this->defaultValues());

        // Decrypt only the secret field
        if (filled($values['similarity_api_secret'] ?? null)) {
            try {
                $values['similarity_api_secret'] = decrypt($values['similarity_api_secret']);
            } catch (\Exception) {
                // Already plain text (e.g. before migration)
            }
        }

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
                Section::make('API Kemiripan Skripsi')
                    ->description('Konfigurasi endpoint untuk mendeteksi kemiripan judul mahasiswa.')
                    ->schema([
                        TextInput::make('similarity_api_url')
                            ->label('URL Endpoint API')
                            ->url()
                            ->required()
                            ->placeholder('http://localhost:8181'),
                        TextInput::make('similarity_api_secret')
                            ->label('Secret Token / API Key')
                            ->password()
                            ->revealable()
                            ->required()
                            ->suffixAction(
                                Action::make('generateSecret')
                                    ->icon('heroicon-m-key')
                                    ->color('warning')
                                    ->tooltip('Generate Secret Baru')
                                    ->requiresConfirmation()
                                    ->modalHeading('Generate Secret API')
                                    ->modalDescription('Apakah Anda yakin ingin membuat secret baru? Secret lama akan diganti setelah Anda menyimpan pengaturan ini.')
                                    ->modalSubmitActionLabel('Ya, Generate')
                                    ->action(function (Set $set) {
                                        $secret = Str::random(32);
                                        $set('similarity_api_secret', $secret);

                                        Notification::make()
                                            ->success()
                                            ->title('Secret baru berhasil dibuat!')
                                            ->body("Berikut adalah secret Anda: **{$secret}**\n\nSilakan salin secret ini sekarang. Setelah halaman ini disimpan, secret akan disembunyikan kembali.")
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
                    ])
                    ->columns(2),

                Section::make('Notifikasi WhatsApp')
                    ->description('Konfigurasi API gateway (contoh: Fonnte/Wablas) untuk pengiriman notifikasi otomatis ke WhatsApp peminjam.')
                    ->schema([
                        TextInput::make('whatsapp_api_url')
                            ->label('URL Endpoint WhatsApp API')
                            ->url()
                            ->placeholder('https://api.fonnte.com/send'),
                        TextInput::make('whatsapp_api_token')
                            ->label('API Token')
                            ->password()
                            ->revealable(),
                    ])
                    ->columns(2),
            ])
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
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

        // Encrypt only the secret before persisting
        $secret = filled($data['similarity_api_secret'] ?? null)
            ? encrypt($data['similarity_api_secret'])
            : null;

        $this->settingRepository()->putMany('integration', [
            'similarity_api_url' => $data['similarity_api_url'] ?? null,
            'similarity_api_secret' => $secret,
            'similarity_api_timeout' => $data['similarity_api_timeout'] ?? 10,
            'whatsapp_api_url' => $data['whatsapp_api_url'] ?? null,
            'whatsapp_api_token' => $data['whatsapp_api_token'] ?? null,
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan integrasi disimpan')
            ->send();

        // Re-fill form with decrypted value so UI stays consistent
        $values = $this->settingRepository()->sectionValues('integration', $this->defaultValues());
        if (filled($values['similarity_api_secret'] ?? null)) {
            try {
                $values['similarity_api_secret'] = decrypt($values['similarity_api_secret']);
            } catch (\Exception) {
            }
        }
        $this->form->fill($values);
    }

    /**
     * @return array<string, string|int|null>
     */
    protected function defaultValues(): array
    {
        return [
            'similarity_api_url' => 'http://localhost:8181',
            'similarity_api_secret' => '',
            'similarity_api_timeout' => 10,
            'whatsapp_api_url' => '',
            'whatsapp_api_token' => '',
        ];
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
