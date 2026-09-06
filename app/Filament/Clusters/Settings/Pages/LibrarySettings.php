<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Repositories\SettingRepository;
use App\Services\ActivityLogService;
use App\Services\KioskPinManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class LibrarySettings extends Page
{
    protected static ?string $navigationLabel = 'Peminjaman & Layanan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Peminjaman, Layanan & Distribusi';

    protected static ?string $slug = 'library';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::BuildingLibrary;

    protected string $view = 'filament.clusters.settings.pages.general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->settingRepository()->sectionValues('library', $this->defaultValues()));
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                Section::make('Periode Layanan Bebas Pustaka')
                    ->schema([
                        Section::make('1. Laporan Kerja Praktik (KP)')
                            ->schema([
                                Toggle::make('distribution_kp_active')
                                    ->label('Buka Periode Pengajuan')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->live(),
                                DatePicker::make('distribution_kp_start')
                                    ->label('Tanggal Mulai')
                                    ->nullable()
                                    ->visible(fn (Get $get): bool => (bool) $get('distribution_kp_active')),
                                DatePicker::make('distribution_kp_end')
                                    ->label('Batas Akhir')
                                    ->nullable()
                                    ->visible(fn (Get $get): bool => (bool) $get('distribution_kp_active')),
                            ])
                            ->columns(3)
                            ->compact(),

                        Section::make('2. Skripsi / Tugas Akhir')
                            ->schema([
                                Toggle::make('distribution_skripsi_active')
                                    ->label('Buka Periode Pengajuan')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->live(),
                                DatePicker::make('distribution_skripsi_start')
                                    ->label('Tanggal Mulai')
                                    ->nullable()
                                    ->visible(fn (Get $get): bool => (bool) $get('distribution_skripsi_active')),
                                DatePicker::make('distribution_skripsi_end')
                                    ->label('Batas Akhir')
                                    ->nullable()
                                    ->visible(fn (Get $get): bool => (bool) $get('distribution_skripsi_active')),
                            ])
                            ->columns(3)
                            ->compact(),

                        Section::make('3. Sumbangan Buku')
                            ->schema([
                                Toggle::make('distribution_book_active')
                                    ->label('Buka Periode Sumbangan')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->live(),
                                DatePicker::make('distribution_book_start')
                                    ->label('Tanggal Mulai')
                                    ->nullable()
                                    ->visible(fn (Get $get): bool => (bool) $get('distribution_book_active')),
                                DatePicker::make('distribution_book_end')
                                    ->label('Batas Akhir')
                                    ->nullable()
                                    ->visible(fn (Get $get): bool => (bool) $get('distribution_book_active')),
                            ])
                            ->columns(3)
                            ->compact(),

                        Textarea::make('distribution_closed_message')
                            ->label('Pemberitahuan Saat Layanan Ditutup')
                            ->placeholder('Contoh: Periode pengajuan bebas pustaka saat ini belum dibuka.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Otentikasi Kiosk Mandiri')
                    ->schema([
                        TextInput::make('kiosk_pin')
                            ->label('PIN Kiosk')
                            ->password()
                            ->revealable()
                            ->helperText('Wajib 6 digit angka. Kosongkan jika tidak diubah.')
                            ->required(fn (): bool => ! $this->kioskPinManager()->isConfigured())
                            ->length(6)
                            ->rule('regex:/^[0-9]{6}$/'),
                    ]),
                Section::make('Kebijakan Peminjaman')
                    ->schema([
                        TextInput::make('loan_max_books')
                            ->label('Batas Kuota Pinjam')
                            ->numeric()
                            ->suffix('buku')
                            ->required()
                            ->minValue(1)
                            ->maxValue(10),
                        TextInput::make('loan_duration_days')
                            ->label('Durasi Peminjaman')
                            ->numeric()
                            ->suffix('hari kerja')
                            ->required()
                            ->minValue(1)
                            ->maxValue(30),
                    ])
                    ->columns(2),
                Section::make('Sanksi Keterlambatan')
                    ->schema([
                        Toggle::make('late_return_suspension_enabled')
                            ->label('Terapkan Pembekuan Hak Pinjam')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                        TextInput::make('late_return_suspend_after_days')
                            ->label('Ambang Batas Keterlambatan')
                            ->numeric()
                            ->suffix('hari')
                            ->required()
                            ->minValue(1)
                            ->maxValue(30)
                            ->default(1)
                            ->visible(fn (Get $get): bool => (bool) $get('late_return_suspension_enabled')),
                        TextInput::make('late_return_cooldown_days')
                            ->label('Masa Penalti Pasca-Pengembalian')
                            ->numeric()
                            ->suffix('hari')
                            ->required()
                            ->minValue(0)
                            ->maxValue(30)
                            ->default(3)
                            ->visible(fn (Get $get): bool => (bool) $get('late_return_suspension_enabled'))
                            ->helperText('0 = langsung aktif.'),
                    ])
                    ->columns(3),
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
        $existingValues = $this->settingRepository()->sectionValues('library', $this->defaultValues());
        $savedValues = [
            'distribution_kp_active' => ! empty($data['distribution_kp_active']) ? '1' : '0',
            'distribution_kp_start' => $data['distribution_kp_start'] ?? null,
            'distribution_kp_end' => $data['distribution_kp_end'] ?? null,
            'distribution_skripsi_active' => ! empty($data['distribution_skripsi_active']) ? '1' : '0',
            'distribution_skripsi_start' => $data['distribution_skripsi_start'] ?? null,
            'distribution_skripsi_end' => $data['distribution_skripsi_end'] ?? null,
            'distribution_book_active' => ! empty($data['distribution_book_active']) ? '1' : '0',
            'distribution_book_start' => $data['distribution_book_start'] ?? null,
            'distribution_book_end' => $data['distribution_book_end'] ?? null,
            'distribution_closed_message' => $data['distribution_closed_message'] ?? null,
            'loan_max_books' => $data['loan_max_books'] ?? 3,
            'loan_duration_days' => $data['loan_duration_days'] ?? 5,
            'late_return_suspension_enabled' => ! empty($data['late_return_suspension_enabled']) ? '1' : '0',
            'late_return_suspend_after_days' => $data['late_return_suspend_after_days'] ?? 1,
            'late_return_cooldown_days' => $data['late_return_cooldown_days'] ?? 3,
        ];

        if (! empty($data['kiosk_pin'])) {
            $this->settingRepository()->put('kiosk', 'pin_hash', Hash::make((string) $data['kiosk_pin']));
        }

        $this->settingRepository()->putMany('library', $savedValues);
        app(ActivityLogService::class)->logSettingsUpdate('library', 'Pengaturan peminjaman & distribusi', $existingValues, $savedValues);

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan')
            ->send();

        $this->form->fill($this->settingRepository()->sectionValues('library', $this->defaultValues()));
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    protected function defaultValues(): array
    {
        return [
            'distribution_kp_active' => true,
            'distribution_kp_start' => null,
            'distribution_kp_end' => null,
            'distribution_skripsi_active' => true,
            'distribution_skripsi_start' => null,
            'distribution_skripsi_end' => null,
            'distribution_book_active' => true,
            'distribution_book_start' => null,
            'distribution_book_end' => null,
            'distribution_closed_message' => 'Periode penyerahan berkas saat ini sedang ditutup oleh pengelola Ruang Baca.',
            'loan_max_books' => '3',
            'loan_duration_days' => '5',
            'late_return_suspension_enabled' => true,
            'late_return_suspend_after_days' => '1',
            'late_return_cooldown_days' => '3',
            'kiosk_pin' => '',
        ];
    }

    protected function kioskPinManager(): KioskPinManager
    {
        return app(KioskPinManager::class);
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
