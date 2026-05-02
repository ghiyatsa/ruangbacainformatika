<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Support\Settings\SettingRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LibrarySettings extends Page
{
    protected static ?string $navigationLabel = 'Aturan Perpustakaan';

    protected static ?string $title = 'Pengaturan Operasional Perpustakaan';

    protected static ?string $slug = 'library';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

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
                Section::make('Aturan Peminjaman')
                    ->description('Batas operasional peminjaman yang dipakai kiosk dan validasi layanan.')
                    ->schema([
                        TextInput::make('loan_max_books')
                            ->label('Maksimal Buku Dipinjam')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText('Jumlah maksimal buku aktif yang boleh dipinjam satu member dalam waktu bersamaan.'),
                        TextInput::make('loan_duration_days')
                            ->label('Durasi Peminjaman (Hari Kerja)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(30)
                            ->helperText('Default operasional pinjam adalah 5 hari kerja. Tempo otomatis melewati Sabtu dan Minggu.'),
                    ])
                    ->columns(2),

                Section::make('Denda & Keterlambatan')
                    ->description('Konfigurasi denda bagi mahasiswa yang terlambat mengembalikan buku.')
                    ->schema([
                        Toggle::make('enable_fines')
                            ->label('Aktifkan Sistem Denda')
                            ->helperText('Jika diaktifkan, sistem akan otomatis menghitung denda keterlambatan pengembalian buku.')
                            ->default(true),
                        TextInput::make('fine_per_day')
                            ->label('Nominal Denda Per Hari (Rp)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(1000)
                            ->prefix('Rp')
                            ->helperText('Contoh: 1000 untuk seribu rupiah per hari per buku.'),
                        TextInput::make('grace_period_days')
                            ->label('Masa Toleransi (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Jumlah hari toleransi setelah jatuh tempo sebelum denda mulai dihitung.'),
                    ])
                    ->columns(2),

                Section::make('Pembatasan Anggota')
                    ->description('Aturan terkait penangguhan atau pemblokiran anggota.')
                    ->schema([
                        Toggle::make('auto_suspend_late_returns')
                            ->label('Suspend Otomatis Keterlambatan')
                            ->helperText('Mencegah peminjaman buku baru bagi anggota yang memiliki tanggungan denda atau buku yang belum dikembalikan melewati batas waktu.')
                            ->default(true),
                        TextInput::make('max_active_fines')
                            ->label('Maksimal Nominal Denda Aktif (Rp)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(50000)
                            ->prefix('Rp')
                            ->helperText('Anggota tidak dapat meminjam jika total denda belum dibayar melebihi jumlah ini.'),
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

        $this->settingRepository()->putMany('library', [
            'loan_max_books' => $data['loan_max_books'] ?? 3,
            'loan_duration_days' => $data['loan_duration_days'] ?? 5,
            'enable_fines' => $data['enable_fines'] ?? true,
            'fine_per_day' => $data['fine_per_day'] ?? 1000,
            'grace_period_days' => $data['grace_period_days'] ?? 0,
            'auto_suspend_late_returns' => $data['auto_suspend_late_returns'] ?? true,
            'max_active_fines' => $data['max_active_fines'] ?? 50000,
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan perpustakaan disimpan')
            ->send();

        $this->form->fill($this->settingRepository()->sectionValues('library', $this->defaultValues()));
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    protected function defaultValues(): array
    {
        return [
            'loan_max_books' => '3',
            'loan_duration_days' => '5',
            'enable_fines' => true,
            'fine_per_day' => 1000,
            'grace_period_days' => 0,
            'auto_suspend_late_returns' => true,
            'max_active_fines' => 50000,
        ];
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
