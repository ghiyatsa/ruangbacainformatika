<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Support\Kiosk\KioskPinManager;
use App\Support\Settings\SettingRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class KioskSettings extends Page
{
    protected static ?string $navigationLabel = 'Kiosk';

    protected static ?string $title = 'Pengaturan Kiosk';

    protected static ?string $slug = 'kiosk';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected string $view = 'filament.clusters.settings.pages.kiosk-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->settingRepository()->sectionValues('kiosk', $this->defaultValues()));
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                Section::make('Akses Kios')
                    ->description('PIN ini wajib dimasukkan perangkat sebelum dapat mengajukan akses kiosk.')
                    ->schema([
                        TextInput::make('pin')
                            ->label('PIN Kiosk')
                            ->password()
                            ->revealable()
                            ->helperText('Kosongkan jika PIN tidak ingin diubah.')
                            ->required(fn (): bool => ! $this->kioskPinManager()->isConfigured())
                            ->minLength(4)
                            ->maxLength(8),
                    ]),
                Section::make('Tampilan Kiosk')
                    ->description('Atur teks dan perilaku dasar yang tampil pada monitor kiosk.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(255),
                        TextInput::make('waiting_message')
                            ->label('Pesan Menunggu')
                            ->maxLength(255),
                        TextInput::make('waiting_refresh_seconds')
                            ->label('Refresh Approval (detik)')
                            ->numeric()
                            ->required()
                            ->minValue(5)
                            ->maxValue(120),
                        TextInput::make('success_redirect_seconds')
                            ->label('Kembali ke Landing (detik)')
                            ->numeric()
                            ->required()
                            ->minValue(3)
                            ->maxValue(30),
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

        if (filled($data['pin'] ?? null)) {
            $this->settingRepository()->put('kiosk', 'pin_hash', Hash::make((string) $data['pin']));
        }

        $this->settingRepository()->putMany('kiosk', [
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'waiting_message' => $data['waiting_message'] ?? null,
            'waiting_refresh_seconds' => $data['waiting_refresh_seconds'] ?? 20,
            'success_redirect_seconds' => $data['success_redirect_seconds'] ?? 6,
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan kios disimpan')
            ->send();

        $this->form->fill($this->settingRepository()->sectionValues('kiosk', $this->defaultValues()));
    }

    /**
     * @return array<string, string>
     */
    protected function defaultValues(): array
    {
        return [
            'pin' => '',
            'title' => 'Pendataan Pengunjung Perpustakaan',
            'subtitle' => 'Silakan masukkan PIN untuk mengaktifkan perangkat kiosk.',
            'waiting_message' => 'Perangkat ini sedang menunggu persetujuan super admin.',
            'waiting_refresh_seconds' => '20',
            'success_redirect_seconds' => '6',
        ];
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }

    protected function kioskPinManager(): KioskPinManager
    {
        return app(KioskPinManager::class);
    }
}
