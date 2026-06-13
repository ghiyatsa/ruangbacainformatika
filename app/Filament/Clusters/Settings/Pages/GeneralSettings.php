<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Repositories\SettingRepository;
use App\Services\ActivityLogService;
use App\Support\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
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
        $this->form->fill($this->formValues());
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
                    ->description('Informasi utama yang tampil di halaman publik.')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Nama Situs')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ruang Baca Teknik Informatika'),
                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->maxLength(255)
                            ->placeholder('Layanan koleksi dan arsip akademik'),
                        TextInput::make('department')
                            ->label('Nama Instansi / Program Studi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Program Studi Teknik Informatika Universitas Malikussaleh'),
                        TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('informatika@unimal.ac.id'),
                        TextInput::make('support_whatsapp')
                            ->label('WhatsApp Bantuan')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('0812xxxxxx')
                            ->helperText('Nomor kontak layanan.'),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Jl. Cot Tengku Nie, Reuleut, Aceh Utara 24355')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('SEO & Metadata')
                    ->description('Nilai default untuk deskripsi halaman, preview tautan, dan metadata publik.')
                    ->schema([
                        Textarea::make('site_description')
                            ->label('Deskripsi Situs')
                            ->required()
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('Layanan katalog, koleksi, dan arsip akademik.'),
                        Textarea::make('site_keywords')
                            ->label('Kata Kunci SEO')
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('perpustakaan digital, teknik informatika, unimal, katalog buku')
                            ->helperText('Pisahkan dengan koma.'),
                        Select::make('seo_robots')
                            ->label('Aturan Index')
                            ->options([
                                'index,follow' => 'Index, Follow',
                                'noindex,follow' => 'No Index, Follow',
                                'noindex,nofollow' => 'No Index, No Follow',
                            ])
                            ->required()
                            ->default('index,follow')
                            ->native(false),
                        ColorPicker::make('theme_color')
                            ->label('Warna Tema')
                            ->required()
                            ->default('#ffffff')
                            ->regex('/^#[0-9A-Fa-f]{6}$/')
                            ->helperText('Gunakan format hex, misalnya #FFFFFF.'),
                    ])
                    ->columns(2),
                Section::make('Notifikasi Global')
                    ->description('Pesan singkat yang tampil di bagian atas beranda.')
                    ->schema([
                        Toggle::make('hero_notice_enabled')
                            ->label('Tampilkan notifikasi')
                            ->helperText('Tampilkan pesan di beranda.')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('gray'),
                        Textarea::make('hero_notice_text')
                            ->label('Teks notifikasi')
                            ->rows(3)
                            ->maxLength(255)
                            ->placeholder('Contoh: Layanan tutup sementara pada hari libur nasional.'),
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
        $existingValues = $this->siteSettings()->values();
        $savedValues = [
            'site_name' => $data['site_name'] ?? null,
            'site_tagline' => $data['site_tagline'] ?? null,
            'site_description' => $data['site_description'] ?? null,
            'department' => $data['department'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'support_whatsapp' => $data['support_whatsapp'] ?? null,
            'address' => $data['address'] ?? null,
            'site_keywords' => $data['site_keywords'] ?? null,
            'seo_robots' => in_array($data['seo_robots'] ?? null, ['index,follow', 'noindex,follow', 'noindex,nofollow'], true)
                ? $data['seo_robots']
                : 'index,follow',
            'theme_color' => preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($data['theme_color'] ?? '')) === 1
                ? strtoupper((string) $data['theme_color'])
                : '#ffffff',
            'hero_notice_enabled' => ($data['hero_notice_enabled'] ?? false) ? '1' : '0',
            'hero_notice_text' => $data['hero_notice_text'] ?? null,
            'hero_notice_url' => $data['hero_notice_url'] ?? null,
            'hero_notice_link_label' => $data['hero_notice_link_label'] ?? null,
            'hero_notice_tone' => in_array($data['hero_notice_tone'] ?? null, ['info', 'warning', 'success'], true)
                ? $data['hero_notice_tone']
                : 'info',
        ];

        $this->settingRepository()->putMany('general', $savedValues);
        app(ActivityLogService::class)->logSettingsUpdate('general', 'Pengaturan umum', $existingValues, $savedValues);

        Notification::make()
            ->success()
            ->title('Pengaturan umum disimpan')
            ->send();

        $this->form->fill($this->formValues());
    }

    /**
     * @return array<string, mixed>
     */
    protected function formValues(): array
    {
        $values = $this->siteSettings()->values();
        $values['hero_notice_enabled'] = $values['hero_notice_enabled'] === '1';

        return $values;
    }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }

    protected function siteSettings(): SiteSettings
    {
        return app(SiteSettings::class);
    }
}
