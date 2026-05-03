<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Support\Settings\SettingRepository;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GeneralSettings extends Page
{
    protected string $view = 'filament.clusters.settings.pages.general-settings';

    protected static ?string $cluster = SettingsCluster::class;

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
                    ->description('Pengaturan dasar yang dipakai di seluruh web.')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Nama Situs')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ruang Baca'),
                        TextInput::make('site_tagline')
                            ->label('Slogan')
                            ->maxLength(255)
                            ->placeholder('Sistem pendataan pengunjung dan layanan perpustakaan'),
                        TextInput::make('support_whatsapp')
                            ->label('WhatsApp Bantuan')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('0812xxxxxx')
                            ->helperText('Nomor kontak yang bisa dihubungi jika ada kendala akses atau layanan.'),
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
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan umum disimpan')
            ->send();

        $this->settingRepository()->forget('general', 'loan_form_isbn_slots');

        $this->form->fill($this->settingRepository()->sectionValues('general', $this->defaultValues()));
    }

    /**
     * @return array<string, string>
     */
    protected function defaultValues(): array
    {
        return [
            'site_name' => config('app.name'),
            'site_tagline' => 'Sistem pendataan pengunjung dan layanan perpustakaan',
            'support_whatsapp' => '',
        ];
    }

    // public function getFormActionsAlignment(): string|Alignment
    // {
    //     return Alignment::Start;
    // }

    protected function settingRepository(): SettingRepository
    {
        return app(SettingRepository::class);
    }
}
