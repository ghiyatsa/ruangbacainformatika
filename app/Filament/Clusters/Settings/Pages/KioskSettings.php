<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\KioskDevice;
use App\Repositories\SettingRepository;
use App\Services\KioskPinManager;
use App\Support\KioskNetworkGuard;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class KioskSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Kios';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Pengaturan Kios';

    protected static ?string $slug = 'kiosk';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ComputerDesktop;

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
                    ->description('PIN digunakan untuk membuka akses sebelum perangkat dipakai oleh pengunjung.')
                    ->schema([
                        FormTextInput::make('pin')
                            ->label('PIN Kios')
                            ->password()
                            ->revealable()
                            ->helperText('Kosongkan jika tidak diubah.')
                            ->required(fn (): bool => ! $this->kioskPinManager()->isConfigured())
                            ->minLength(4)
                            ->maxLength(8),
                        Textarea::make('allowed_networks')
                            ->label('Allowlist IP / Subnet')
                            ->rows(4)
                            ->placeholder("127.0.0.1\n192.168.10.0/24\n10.10.0.0/16")
                            ->helperText('Kosongkan untuk mengizinkan semua jaringan. Pisahkan IP atau CIDR dengan baris baru atau koma.')
                            ->rule(function (): Closure {
                                return function (string $attribute, mixed $value, Closure $fail): void {
                                    $invalidNetworks = collect($this->kioskNetworkGuard()->normalizeNetworks($value))
                                        ->reject(fn (string $network): bool => $this->kioskNetworkGuard()->isValidNetwork($network))
                                        ->values();

                                    if ($invalidNetworks->isNotEmpty()) {
                                        $fail('Format jaringan tidak valid: '.Arr::join($invalidNetworks->all(), ', '));
                                    }
                                };
                            }),
                    ]),

                Section::make('Perangkat Aktif')
                    ->description('Daftar perangkat yang saat ini memiliki sesi kios aktif.')
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
                Section::make('Tampilan Kios')
                    ->description('Atur teks utama yang tampil pada layar layanan mandiri.')
                    ->schema([
                        FormTextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Pendataan Pengunjung Perpustakaan'),
                        FormTextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(255)
                            ->placeholder('Silakan masukkan PIN untuk mengaktifkan perangkat.'),
                    ])
                    ->columns(2),
            ])
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('rotateSessions')
                            ->label('Reset Sesi Perangkat')
                            ->color('gray')
                            ->icon(Heroicon::OutlinedArrowPath)
                            ->requiresConfirmation()
                            ->modalHeading('Reset Semua Sesi Kios')
                            ->modalDescription('Semua perangkat akan diminta memasukkan PIN kembali.')
                            ->modalSubmitActionLabel('Reset Sesi')
                            ->action('rotateSessions'),
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
        $pinWasUpdated = filled($data['pin'] ?? null);

        if ($pinWasUpdated) {
            $this->settingRepository()->put('kiosk', 'pin_hash', Hash::make((string) $data['pin']));
            $this->kioskPinManager()->rotateSessions();
        }

        $this->settingRepository()->putMany('kiosk', [
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'allowed_networks' => collect($this->kioskNetworkGuard()->normalizeNetworks($data['allowed_networks'] ?? ''))->implode(PHP_EOL),
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan kios disimpan')
            ->body($pinWasUpdated ? 'PIN diperbarui dan semua sesi perangkat lama telah direset.' : null)
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
            'allowed_networks' => '',
            'title' => 'Pendataan Pengunjung Perpustakaan',
            'subtitle' => 'Silakan masukkan PIN untuk mengaktifkan perangkat kios.',
        ];
    }

    public function rotateSessions(): void
    {
        $this->kioskPinManager()->rotateSessions();

        Notification::make()
            ->success()
            ->title('Semua sesi kios telah direset')
            ->body('Semua perangkat perlu memasukkan PIN kembali.')
            ->send();

        $this->form->fill($this->settingRepository()->sectionValues('kiosk', $this->defaultValues()));
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(KioskDevice::query())
            ->searchable(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perangkat')
                    ->placeholder('Belum diberi nama')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->searchable(),
                TextColumn::make('user_agent')
                    ->label('Browser / Sistem Operasi')
                    ->wrap()
                    ->size('xs')
                    ->color('gray'),
                TextColumn::make('last_active_at')
                    ->label('Terakhir Aktif')
                    ->dateTime()
                    ->color('gray')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->icon(Heroicon::Pencil)
                    ->modalWidth('sm')
                    ->modalHeading('Ubah Nama Perangkat')
                    ->modalSubmitActionLabel('Simpan')
                    ->schema([
                        FormTextInput::make('name')
                            ->label('Nama Perangkat')
                            ->required(),
                    ]),
                Action::make('revoke')
                    ->hiddenLabel()
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Keluarkan Perangkat')
                    ->modalDescription('Perangkat ini harus memasukkan PIN lagi untuk digunakan.')
                    ->modalSubmitActionLabel('Keluarkan')
                    ->action(fn (Action $action) => $action->getRecord()->delete()),
            ])
            ->emptyStateHeading('Tidak ada perangkat aktif')
            ->emptyStateDescription('Perangkat aktif akan tampil di sini.');
    }

    protected function kioskPinManager(): KioskPinManager
    {
        return app(KioskPinManager::class);
    }

    protected function kioskNetworkGuard(): KioskNetworkGuard
    {
        return app(KioskNetworkGuard::class);
    }
}
