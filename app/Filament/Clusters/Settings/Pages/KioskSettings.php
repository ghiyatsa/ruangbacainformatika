<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\KioskDevice;
use App\Repositories\SettingRepository;
use App\Services\KioskPinManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
use Illuminate\Support\Facades\Hash;

class KioskSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Kiosk';

    protected static ?string $title = 'Pengaturan Kiosk';

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
                    ->description('PIN ini wajib dimasukkan sebelum pengunjung dapat menggunakan kiosk.')
                    ->schema([
                        FormTextInput::make('pin')
                            ->label('PIN Kiosk')
                            ->password()
                            ->revealable()
                            ->helperText('Kosongkan jika PIN tidak ingin diubah.')
                            ->required(fn (): bool => ! $this->kioskPinManager()->isConfigured())
                            ->minLength(4)
                            ->maxLength(8),
                    ]),

                Section::make('Daftar Perangkat Kiosk')
                    ->description('Daftar browser yang saat ini memiliki sesi kiosk aktif.')
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
                Section::make('Tampilan Kiosk')
                    ->description('Atur teks dan perilaku dasar yang tampil pada monitor kiosk.')
                    ->schema([
                        FormTextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        FormTextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(255),
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
        ];
    }

    public function rotateSessions(): void
    {
        $this->kioskPinManager()->rotateSessions();

        Notification::make()
            ->success()
            ->title('Semua sesi kiosk telah direset')
            ->body('Browser kiosk aktif akan diminta memasukkan PIN ulang.')
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
                    ->placeholder('Tanpa Nama')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
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
                    ->modalDescription('Apakah Anda yakin ingin mengeluarkan perangkat ini dari sesi kiosk?')
                    ->action(fn (Action $action) => $action->getRecord()->delete()),
            ])
            ->emptyStateHeading('Tidak ada perangkat aktif')
            ->emptyStateDescription('Belum ada browser yang masuk ke mode kiosk.');
    }

    protected function kioskPinManager(): KioskPinManager
    {
        return app(KioskPinManager::class);
    }
}
