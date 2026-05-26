<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Repositories\SettingRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettings extends Page
{
    protected static ?string $navigationLabel = 'Umum';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Pengaturan Umum';

    protected string $view = 'filament.clusters.settings.pages.general-settings';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::GlobeAlt;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->settingRepository()->sectionValues('general', $this->defaultValues()));
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                Section::make('Identitas Situs')
                    ->description('Informasi utama yang tampil pada layanan perpustakaan.')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Nama Situs')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ruang Baca'),
                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->maxLength(255)
                            ->placeholder('Layanan perpustakaan yang rapi dan mudah diakses'),
                        TextInput::make('support_whatsapp')
                            ->label('WhatsApp Bantuan')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('0812xxxxxx')
                            ->helperText('Nomor kontak bantuan.'),
                    ])
                    ->columns(2),
                Section::make('Notifikasi Global')
                    ->description('Informasi singkat yang bisa ditampilkan pada hero halaman beranda.')
                    ->schema([
                        Toggle::make('hero_notice_enabled')
                            ->label('Tampilkan notifikasi')
                            ->helperText('Aktifkan untuk menampilkan info singkat di atas badge hero.')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('gray'),
                        Textarea::make('hero_notice_text')
                            ->label('Teks notifikasi')
                            ->rows(3)
                            ->maxLength(255)
                            ->placeholder('Contoh: Layanan perpustakaan tutup sementara pada hari libur nasional.'),
                        TextInput::make('hero_notice_url')
                            ->label('URL tujuan')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/pengumuman'),
                        TextInput::make('hero_notice_link_label')
                            ->label('Label tautan')
                            ->maxLength(50)
                            ->placeholder('Lihat detail'),
                        Select::make('hero_notice_tone')
                            ->label('Warna notifikasi')
                            ->options([
                                'info' => 'Info',
                                'warning' => 'Peringatan',
                                'success' => 'Sukses',
                            ])
                            ->required()
                            ->default('info')
                            ->native(false),
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

        $this->settingRepository()->putMany('general', [
            'site_name' => $data['site_name'] ?? null,
            'site_tagline' => $data['site_tagline'] ?? null,
            'support_whatsapp' => $data['support_whatsapp'] ?? null,
            'hero_notice_enabled' => ($data['hero_notice_enabled'] ?? false) ? '1' : '0',
            'hero_notice_text' => $data['hero_notice_text'] ?? null,
            'hero_notice_url' => $data['hero_notice_url'] ?? null,
            'hero_notice_link_label' => $data['hero_notice_link_label'] ?? null,
            'hero_notice_tone' => in_array($data['hero_notice_tone'] ?? null, ['info', 'warning', 'success'], true)
                ? $data['hero_notice_tone']
                : 'info',
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan umum disimpan')
            ->send();

        $this->form->fill($this->settingRepository()->sectionValues('general', $this->defaultValues()));
    }

    /**
     * @return array<string, string>
     */
    protected function defaultValues(): array
    {
        return [
            'site_name' => config('app.name'),
            'site_tagline' => 'Layanan perpustakaan yang rapi dan mudah diakses',
            'support_whatsapp' => '',
            'hero_notice_enabled' => '0',
            'hero_notice_text' => '',
            'hero_notice_url' => '',
            'hero_notice_link_label' => '',
            'hero_notice_tone' => 'info',
        ];
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
