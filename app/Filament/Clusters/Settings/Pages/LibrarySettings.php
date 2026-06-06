<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Repositories\SettingRepository;
use App\Services\ActivityLogService;
use BackedEnum;
use Filament\Actions\Action;
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

class LibrarySettings extends Page
{
    protected static ?string $navigationLabel = 'Aturan Sirkulasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pengaturan Peminjaman';

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
                Section::make('Aturan Peminjaman')
                    ->description('Aturan dasar peminjaman untuk layanan anggota.')
                    ->schema([
                        TextInput::make('loan_max_books')
                            ->label('Maksimal Buku Dipinjam')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText('Batas pinjaman aktif per anggota.'),
                        TextInput::make('loan_duration_days')
                            ->label('Durasi Peminjaman (Hari Kerja)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(30)
                            ->helperText('Durasi dihitung dalam hari kerja.'),
                    ])
                    ->columns(2),
                Section::make('Pembatasan Keterlambatan')
                    ->description('Aturan ini membatasi peminjaman saat ada keterlambatan.')
                    ->schema([
                        Toggle::make('late_return_suspension_enabled')
                            ->label('Aktifkan pembatasan peminjaman')
                            ->helperText('Anggota yang terlambat akan dibatasi sementara.')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                        TextInput::make('late_return_suspend_after_days')
                            ->label('Mulai Berlaku Setelah Telat (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(30)
                            ->default(1)
                            ->visible(fn (Get $get): bool => (bool) $get('late_return_suspension_enabled'))
                            ->helperText('Contoh: isi 1 jika pembatasan mulai berlaku setelah telat 1 hari.'),
                        TextInput::make('late_return_cooldown_days')
                            ->label('Masa Pembatasan Setelah Pengembalian (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(30)
                            ->default(3)
                            ->visible(fn (Get $get): bool => (bool) $get('late_return_suspension_enabled'))
                            ->helperText('Isi 0 jika pembatasan berhenti saat buku dikembalikan.'),
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
            'loan_max_books' => $data['loan_max_books'] ?? 3,
            'loan_duration_days' => $data['loan_duration_days'] ?? 5,
            'late_return_suspension_enabled' => ! empty($data['late_return_suspension_enabled']) ? '1' : '0',
            'late_return_suspend_after_days' => $data['late_return_suspend_after_days'] ?? 1,
            'late_return_cooldown_days' => $data['late_return_cooldown_days'] ?? 3,
        ];

        $this->settingRepository()->putMany('library', $savedValues);
        app(ActivityLogService::class)->logSettingsUpdate('library', 'Pengaturan peminjaman', $existingValues, $savedValues);

        Notification::make()
            ->success()
            ->title('Pengaturan peminjaman disimpan')
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
            'late_return_suspension_enabled' => true,
            'late_return_suspend_after_days' => '1',
            'late_return_cooldown_days' => '3',
        ];
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
