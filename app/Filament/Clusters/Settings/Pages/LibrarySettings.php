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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LibrarySettings extends Page
{
    protected static ?string $navigationLabel = 'Aturan Sirkulasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pengaturan Operasional Perpustakaan';

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
                    ->description('Aturan dasar peminjaman yang dipakai pada layanan mandiri dan proses administrasi.')
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
                            ->helperText('Dihitung dalam hari kerja.'),
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
        ];
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
