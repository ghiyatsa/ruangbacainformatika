<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Resources\Authors\Schemas\AuthorForm;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Publishers\Schemas\PublisherForm;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\DocumentSubmission;
use App\Models\Publisher;
use App\Services\DocumentDistributionService;
use App\Support\Isbn;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DocumentDistributionPage extends Page
{
    protected static ?string $navigationLabel = 'Distribusi & Bebas Pustaka';

    protected static ?string $title = 'Penyerahan Mandiri & Bebas Pustaka';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.dashboard.pages.document-distribution-page';

    public ?array $data = [];

    public ?DocumentSubmission $kpSubmission = null;

    public ?DocumentSubmission $skripsiSubmission = null;

    /** @var Collection<int, DocumentSubmission> */
    public Collection $bookSubmissions;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->isMahasiswa();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $allSubmissions = DocumentSubmission::query()
            ->with(['submittable'])
            ->where('user_id', $user->id)
            ->get();

        $this->kpSubmission = $allSubmissions->firstWhere('type', DocumentSubmission::TYPE_INTERNSHIP_REPORT);
        $this->skripsiSubmission = $allSubmissions->firstWhere('type', DocumentSubmission::TYPE_SKRIPSI);
        $this->bookSubmissions = $allSubmissions->where('type', DocumentSubmission::TYPE_BOOK_DONATION)->values();

        $defaultIdentity = [
            'student_name' => $user->name,
            'student_id' => $user->identityNumber(),
            'year' => (int) date('Y'),
        ];

        // KP Data
        $kpData = $defaultIdentity;
        if ($this->kpSubmission) {
            $kpData = [
                'student_name' => $user->name,
                'student_id' => $user->identityNumber(),
                'title' => $this->kpSubmission->title,
                'company_name' => $this->kpSubmission->company_name,
                'company_address' => $this->kpSubmission->company_address,
                'academic_advisor' => $this->kpSubmission->academic_advisor,
                'field_advisor' => $this->kpSubmission->field_advisor,
                'year' => $this->kpSubmission->year,
                'abstract' => $this->kpSubmission->abstract,
                'keywords' => $this->kpSubmission->keywords,
                'document_file_path' => $this->kpSubmission->document_file_path,
                'endorsement_file_path' => $this->kpSubmission->endorsement_file_path,
            ];
        }

        // Skripsi Data
        $skripsiData = $defaultIdentity;
        if ($this->skripsiSubmission) {
            $skripsiData = [
                'student_name' => $user->name,
                'student_id' => $user->identityNumber(),
                'title' => $this->skripsiSubmission->title,
                'academic_advisor' => $this->skripsiSubmission->academic_advisor,
                'year' => $this->skripsiSubmission->year,
                'abstract' => $this->skripsiSubmission->abstract,
                'keywords' => $this->skripsiSubmission->keywords,
                'document_file_path' => $this->skripsiSubmission->document_file_path,
                'endorsement_file_path' => $this->skripsiSubmission->endorsement_file_path,
            ];
        }

        // Books Repeater Data
        $booksData = [
            'items' => [
                [
                    'book_condition' => 'good',
                    'copies_count' => 1,
                    'language' => 'Indonesia',
                    'published_year' => (int) date('Y'),
                ],
            ],
        ];

        if ($this->bookSubmissions->isNotEmpty()) {
            $booksData = [
                'items' => $this->bookSubmissions->map(fn (DocumentSubmission $sub) => [
                    'id' => $sub->id,
                    'book_source' => $sub->submittable_id ? 'existing' : 'new',
                    'existing_book_id' => $sub->submittable_id,
                    'status' => $sub->status,
                    'status_label' => $sub->statusLabel(),
                    'status_color' => $sub->statusColor(),
                    'receipt_number' => $sub->receipt_number,
                    'receipt_token' => $sub->receipt_token,
                    'revision_notes' => $sub->revision_notes,
                    'title' => $sub->title,
                    'subtitle' => $sub->subtitle,
                    'slug' => $sub->slug,
                    'description' => $sub->description,
                    'isbn' => $sub->isbn,
                    'issn' => $sub->issn,
                    'ddc_code' => $sub->ddc_code,
                    'language' => $sub->language ?: 'Indonesia',
                    'publisher_id' => $sub->publisher_id,
                    'published_year' => $sub->year,
                    'edition' => $sub->edition,
                    'pages' => $sub->pages,
                    'authors' => $sub->author_ids ?? [],
                    'categories' => $sub->category_ids ?? [],
                    'book_condition' => $sub->book_condition ?: 'good',
                    'copies_count' => $sub->copies_count ?: 1,
                    'cover_image' => $sub->cover_image,
                    'endorsement_file_path' => $sub->endorsement_file_path,
                ])->all(),
            ];
        }

        $this->data = [
            'kp' => $kpData,
            'skripsi' => $skripsiData,
            'books' => $booksData,
        ];

        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        $distributionService = app(DocumentDistributionService::class);
        $isAnyOpen = $distributionService->isPeriodOpen();
        $isKpOpen = $distributionService->isPeriodOpen(DocumentSubmission::TYPE_INTERNSHIP_REPORT);
        $isSkripsiOpen = $distributionService->isPeriodOpen(DocumentSubmission::TYPE_SKRIPSI);
        $isBookOpen = $distributionService->isPeriodOpen(DocumentSubmission::TYPE_BOOK_DONATION);
        $closedMessage = $distributionService->periodClosedMessage();

        return $schema
            ->components([
                Section::make('Informasi Periode Layanan')
                    ->description($closedMessage)
                    ->icon(Heroicon::OutlinedExclamationCircle)
                    ->iconColor('danger')
                    ->visible(! $isAnyOpen)
                    ->columnSpanFull()
                    ->schema([]),

                Tabs::make('DistribusiTabs')
                    ->tabs([
                        $this->buildInternshipTab($isKpOpen),
                        $this->buildSkripsiTab($isSkripsiOpen),
                        $this->buildBookTab($isBookOpen),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function buildInternshipTab(bool $isOpen = true): Tab
    {
        $submission = $this->kpSubmission;
        $isApproved = (bool) $submission?->isApproved();
        $isRevision = (bool) $submission?->isRevision();
        $isPending = (bool) $submission?->isPending();
        $isFormDisabled = $isApproved || (! $isOpen && ! $isRevision);

        return Tab::make('Laporan Kerja Praktik (KP)')
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->badge($submission ? $submission->statusLabel() : 'Belum')
            ->badgeColor($submission ? $submission->statusColor() : 'gray')
            ->schema([
                Form::make([
                    Section::make('Laporan Kerja Praktik Disetujui')
                        ->description(fn (): string => 'Laporan KP telah diverifikasi dan diterbitkan di katalog resmi.'.($submission?->receipt_number ? ' Nomor Tanda Terima: '.$submission->receipt_number : ''))
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->iconColor('success')
                        ->visible(fn (): bool => $isApproved)
                        ->headerActions([
                            Action::make('printKpReceipt')
                                ->label('Cetak Tanda Terima KP')
                                ->icon(Heroicon::OutlinedPrinter)
                                ->url(fn () => route('distribution.receipt', ['token' => $submission?->receipt_token]), shouldOpenInNewTab: true),
                        ])
                        ->schema([]),

                    Section::make('Pengajuan Memerlukan Revisi')
                        ->description(fn (): string => 'Catatan Petugas: '.($submission?->revision_notes ?? '-').'. Mohon perbaiki data atau berkas sesuai arahan di atas, kemudian ajukan kembali.')
                        ->icon(Heroicon::OutlinedExclamationTriangle)
                        ->iconColor('danger')
                        ->visible(fn (): bool => $isRevision)
                        ->schema([]),

                    Section::make('Menunggu Verifikasi Petugas')
                        ->description('Berkas laporan KP sedang dalam antrean pemeriksaan petugas perpustakaan.')
                        ->icon(Heroicon::OutlinedClock)
                        ->iconColor('warning')
                        ->visible(fn (): bool => $isPending)
                        ->schema([]),

                    Section::make('Data Laporan KP')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('kp.student_name')->label('Nama Mahasiswa')->required()->disabled($isFormDisabled),
                                TextInput::make('kp.student_id')->label('NIM')->disabled()->dehydrated(false),
                            ]),
                            TextInput::make('kp.title')->label('Judul Laporan KP')->required()->disabled($isFormDisabled)->columnSpanFull(),
                            Grid::make(2)->schema([
                                TextInput::make('kp.company_name')->label('Instansi / Perusahaan')->required()->disabled($isFormDisabled),
                                TextInput::make('kp.company_address')->label('Alamat Instansi')->disabled($isFormDisabled),
                            ]),
                            Grid::make(3)->schema([
                                TextInput::make('kp.academic_advisor')->label('Dosen Pembimbing KP')->required()->disabled($isFormDisabled),
                                TextInput::make('kp.field_advisor')->label('Pembimbing Lapangan')->disabled($isFormDisabled),
                                TextInput::make('kp.year')->label('Tahun Pelaksanaan')->numeric()->required()->disabled($isFormDisabled),
                            ]),
                            Textarea::make('kp.abstract')->label('Abstrak / Ringkasan')->rows(4)->required()->disabled($isFormDisabled)->columnSpanFull(),
                            TextInput::make('kp.keywords')->label('Kata Kunci')->disabled($isFormDisabled)->columnSpanFull(),
                        ]),

                    Section::make('Berkas Laporan KP')
                        ->schema([
                            FileUpload::make('kp.document_file_path')
                                ->label('Berkas Laporan KP (PDF)')
                                ->disk('documents')
                                ->directory('submissions/internship_report')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(20480)
                                ->required(fn () => $this->kpSubmission === null)
                                ->disabled($isFormDisabled),
                            FileUpload::make('kp.endorsement_file_path')
                                ->label('Lembar Pengesahan (Bertanda Tangan)')
                                ->disk('documents')
                                ->directory('submissions/endorsements')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->disabled($isFormDisabled),
                        ]),
                ])
                    ->livewireSubmitHandler('submitKp')
                    ->footer(
                        (! $isApproved && ($isOpen || $isRevision)) ? [
                            Actions::make([
                                Action::make('submitKpBtn')
                                    ->label($this->kpSubmission ? 'Simpan Perubahan Laporan KP' : 'Ajukan Laporan KP')
                                    ->submit('submitKp')
                                    ->size('lg'),
                            ]),
                        ] : []
                    ),
            ]);
    }

    protected function buildSkripsiTab(bool $isOpen = true): Tab
    {
        $submission = $this->skripsiSubmission;
        $isApproved = (bool) $submission?->isApproved();
        $isRevision = (bool) $submission?->isRevision();
        $isPending = (bool) $submission?->isPending();
        $isFormDisabled = $isApproved || (! $isOpen && ! $isRevision);

        return Tab::make('Skripsi / Tugas Akhir')
            ->icon(Heroicon::OutlinedAcademicCap)
            ->badge($submission ? $submission->statusLabel() : 'Belum')
            ->badgeColor($submission ? $submission->statusColor() : 'gray')
            ->schema([
                Form::make([
                    Section::make('Naskah Skripsi Disetujui')
                        ->description(fn (): string => 'Naskah Skripsi telah diverifikasi dan diterbitkan di katalog resmi.'.($submission?->receipt_number ? ' Nomor Tanda Terima: '.$submission->receipt_number : ''))
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->iconColor('success')
                        ->visible(fn (): bool => $isApproved)
                        ->headerActions([
                            Action::make('printSkripsiReceipt')
                                ->label('Cetak Tanda Terima Skripsi')
                                ->icon(Heroicon::OutlinedPrinter)
                                ->url(fn () => route('distribution.receipt', ['token' => $submission?->receipt_token]), shouldOpenInNewTab: true),
                        ])
                        ->schema([]),

                    Section::make('Pengajuan Memerlukan Revisi')
                        ->description(fn (): string => 'Catatan Petugas: '.($submission?->revision_notes ?? '-').'. Mohon perbaiki naskah sesuai arahan di atas, kemudian ajukan kembali.')
                        ->icon(Heroicon::OutlinedExclamationTriangle)
                        ->iconColor('danger')
                        ->visible(fn (): bool => $isRevision)
                        ->schema([]),

                    Section::make('Menunggu Verifikasi Petugas')
                        ->description('Naskah Skripsi sedang dalam antrean pemeriksaan petugas perpustakaan.')
                        ->icon(Heroicon::OutlinedClock)
                        ->iconColor('warning')
                        ->visible(fn (): bool => $isPending)
                        ->schema([]),

                    Section::make('Data Naskah Skripsi')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('skripsi.student_name')->label('Nama Mahasiswa')->required()->disabled($isFormDisabled),
                                TextInput::make('skripsi.student_id')->label('NIM')->disabled()->dehydrated(false),
                            ]),
                            TextInput::make('skripsi.title')->label('Judul Skripsi')->required()->disabled($isFormDisabled)->columnSpanFull(),
                            Grid::make(2)->schema([
                                TextInput::make('skripsi.academic_advisor')->label('Dosen Pembimbing')->required()->disabled($isFormDisabled),
                                TextInput::make('skripsi.year')->label('Tahun Lulus / Sidang')->numeric()->required()->disabled($isFormDisabled),
                            ]),
                            Textarea::make('skripsi.abstract')->label('Abstrak Skripsi')->rows(5)->required()->disabled($isFormDisabled)->columnSpanFull(),
                            TextInput::make('skripsi.keywords')->label('Kata Kunci')->disabled($isFormDisabled)->columnSpanFull(),
                        ]),

                    Section::make('Berkas Naskah Skripsi')
                        ->schema([
                            FileUpload::make('skripsi.document_file_path')
                                ->label('Berkas Naskah Lengkap Skripsi (PDF)')
                                ->disk('documents')
                                ->directory('submissions/skripsi')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(30720)
                                ->required(fn () => $this->skripsiSubmission === null)
                                ->disabled($isFormDisabled),
                            FileUpload::make('skripsi.endorsement_file_path')
                                ->label('Lembar Pengesahan Sidang (Bertanda Tangan)')
                                ->disk('documents')
                                ->directory('submissions/endorsements')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->disabled($isFormDisabled),
                        ]),
                ])
                    ->livewireSubmitHandler('submitSkripsi')
                    ->footer(
                        (! $isApproved && ($isOpen || $isRevision)) ? [
                            Actions::make([
                                Action::make('submitSkripsiBtn')
                                    ->label($this->skripsiSubmission ? 'Simpan Perubahan Skripsi' : 'Ajukan Naskah Skripsi')
                                    ->submit('submitSkripsi')
                                    ->size('lg'),
                            ]),
                        ] : []
                    ),
            ]);
    }

    protected function buildBookTab(bool $isOpen = true): Tab
    {
        $bookCount = count($this->data['books']['items'] ?? []);

        return Tab::make('Sumbangan Buku')
            ->icon(Heroicon::OutlinedBookOpen)
            ->badge($bookCount > 0 ? "{$bookCount} Judul" : 'Belum')
            ->badgeColor('purple')
            ->schema([
                Form::make([
                    Section::make('Panduan Sumbangan Buku')
                        ->description('Verifikasi fisik buku dilakukan oleh petugas di ruang baca.')
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->columnSpanFull()
                        ->schema([]),

                    Repeater::make('books.items')
                        ->label('Daftar Buku Sumbangan')
                        ->addActionLabel('+ Tambah Judul Buku')
                        ->itemLabel(fn (array $state): ?string => filled($state['title'] ?? null) ? $state['title'].(isset($state['status_label']) ? " ({$state['status_label']})" : '') : 'Judul Buku Baru')
                        ->collapsible()
                        ->defaultItems(1)
                        ->disabled(! $isOpen)
                        ->schema([
                            Section::make('Perlu Revisi')
                                ->description(fn (Get $get): string => 'Catatan Petugas: '.($get('revision_notes') ?? '-').'. Mohon sesuaikan data buku atau foto serah terima.')
                                ->icon(Heroicon::OutlinedExclamationTriangle)
                                ->iconColor('danger')
                                ->visible(fn (Get $get): bool => ($get('status') ?? '') === DocumentSubmission::STATUS_REVISION)
                                ->schema([]),

                            Radio::make('book_source')
                                ->label('Sumber Buku')
                                ->options([
                                    'existing' => 'Pilih Buku yang Sudah Ada di Katalog',
                                    'new' => 'Buku Baru (Belum Terdaftar)',
                                ])
                                ->default('existing')
                                ->live()
                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                    if ($state === 'new') {
                                        $set('existing_book_id', null);
                                    }
                                })
                                ->inline(),

                            Select::make('existing_book_id')
                                ->label('Cari Buku di Katalog')
                                ->placeholder('Ketik judul atau ISBN buku...')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search): array {
                                    return Book::query()
                                        ->where('title', 'like', "%{$search}%")
                                        ->orWhere('isbn', 'like', "%{$search}%")
                                        ->limit(30)
                                        ->get()
                                        ->mapWithKeys(fn (Book $book) => [
                                            $book->id => $book->title.($book->isbn ? " (ISBN: {$book->isbn})" : ''),
                                        ])
                                        ->all();
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $b = Book::find($value);

                                    return $b ? $b->title.($b->isbn ? " (ISBN: {$b->isbn})" : '') : null;
                                })
                                ->live()
                                ->afterStateUpdated(function (Set $set, ?int $state): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $book = Book::with(['authors', 'categories'])->find($state);
                                    if (! $book) {
                                        return;
                                    }

                                    $set('title', $book->title);
                                    $set('subtitle', $book->subtitle);
                                    $set('slug', $book->slug);
                                    $set('description', $book->description);
                                    $set('isbn', $book->isbn);
                                    $set('issn', $book->issn);
                                    $set('ddc_code', $book->ddc_code);
                                    $set('language', $book->language ?: 'Indonesia');
                                    $set('publisher_id', $book->publisher_id);
                                    $set('published_year', $book->published_year);
                                    $set('edition', $book->edition);
                                    $set('pages', $book->pages);
                                    $set('authors', $book->authors->pluck('id')->all());
                                    $set('categories', $book->categories->pluck('id')->all());
                                    $set('cover_image', $book->cover_image);
                                })
                                ->visible(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                ->required(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing'),

                            Grid::make([
                                'default' => 1,
                                'lg' => 3,
                            ])->schema([
                                Group::make()->schema([
                                    Section::make('Identitas Buku')
                                        ->visible(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'new' || filled($get('existing_book_id')))
                                        ->schema([
                                            TextInput::make('title')
                                                ->label('Judul Buku')
                                                ->required()
                                                ->minLength(3)
                                                ->maxLength(255)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Book::generateSlugPreview($state)))
                                                ->placeholder('Contoh: Machine Learning Praktis'),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->disabled()
                                                ->dehydrated()
                                                ->required()
                                                ->maxLength(255),

                                            TextInput::make('subtitle')
                                                ->label('Subjudul')
                                                ->maxLength(255)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder('Subjudul tambahan bila ada')
                                                ->columnSpanFull(),

                                            Textarea::make('description')
                                                ->label('Sinopsis Singkat')
                                                ->rows(3)
                                                ->maxLength(1500)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->columnSpanFull(),

                                            TextInput::make('isbn')
                                                ->label('ISBN')
                                                ->nullable()
                                                ->minLength(8)
                                                ->maxLength(13)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder('9786020000001')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                    $normalized = Isbn::normalize($state);
                                                    $set('isbn', $normalized);
                                                    if (filled($normalized)) {
                                                        $set('issn', null);
                                                    }
                                                }),

                                            TextInput::make('issn')
                                                ->label('ISSN')
                                                ->nullable()
                                                ->maxLength(20)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder('1234-5678')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                    $normalized = filled($state) ? trim((string) $state) : null;
                                                    $set('issn', $normalized);
                                                    if (filled($normalized)) {
                                                        $set('isbn', null);
                                                    }
                                                }),

                                            TextInput::make('ddc_code')
                                                ->label('Kode DDC')
                                                ->maxLength(20)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder('000-999'),

                                            TextInput::make('language')
                                                ->label('Bahasa Dokumen')
                                                ->default('Indonesia')
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->maxLength(30),
                                        ])->columns(2),

                                    Section::make('Detail Publikasi')
                                        ->visible(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'new' || filled($get('existing_book_id')))
                                        ->schema([
                                            Select::make('publisher_id')
                                                ->label('Penerbit')
                                                ->options(fn () => Publisher::query()->pluck('name', 'id'))
                                                ->placeholder('Pilih Penerbit')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->createOptionForm(PublisherForm::optionFormSchema())
                                                ->createOptionUsing(fn (array $data): int => static::createPublisher($data)),

                                            TextInput::make('edition')
                                                ->label('Edisi / Volume')
                                                ->maxLength(255)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder('Edisi Revisi atau Vol. 1'),

                                            TextInput::make('published_year')
                                                ->label('Tahun Terbit')
                                                ->numeric()
                                                ->integer()
                                                ->minValue(1000)
                                                ->maxValue(now()->year)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder((string) now()->year),

                                            TextInput::make('pages')
                                                ->label('Jumlah Halaman')
                                                ->maxLength(255)
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->placeholder('Contoh: 250'),

                                            Select::make('authors')
                                                ->label('Penulis')
                                                ->options(fn () => Author::query()->pluck('name', 'id'))
                                                ->placeholder('Pilih Penulis')
                                                ->multiple()
                                                ->searchable()
                                                ->preload()
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->createOptionForm(AuthorForm::optionFormSchema())
                                                ->createOptionUsing(fn (array $data): int => static::createAuthor($data)),

                                            Select::make('categories')
                                                ->label('Kategori')
                                                ->options(fn () => Category::query()->pluck('name', 'id'))
                                                ->placeholder('Pilih Kategori')
                                                ->multiple()
                                                ->searchable()
                                                ->preload()
                                                ->disabled(fn (Get $get): bool => ($get('book_source') ?? 'existing') === 'existing')
                                                ->dehydrated()
                                                ->createOptionForm(CategoryForm::optionFormSchema())
                                                ->createOptionUsing(fn (array $data): int => static::createCategory($data)),
                                        ])->columns(2),
                                ])->columnSpan(['lg' => 2]),

                                Group::make()->schema([
                                    Section::make('Sampul Buku')
                                        ->schema([
                                            FileUpload::make('cover_image')
                                                ->label('Cover Depan')
                                                ->image()
                                                ->directory('books/covers')
                                                ->disk('public')
                                                ->imagePreviewHeight('240')
                                                ->maxSize(2048)
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                                        ]),

                                    Section::make('Fisik & Penyerahan')
                                        ->schema([
                                            TextInput::make('copies_count')
                                                ->label('Jumlah Eksemplar')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->required(),

                                            Select::make('book_condition')
                                                ->label('Kondisi Fisik')
                                                ->options([
                                                    'good' => 'Sangat Baik / Baru',
                                                    'fair' => 'Baik / Layak Baca',
                                                ])
                                                ->default('good')
                                                ->required(),

                                            FileUpload::make('endorsement_file_path')
                                                ->label('Foto Fisik Buku / Bukti Serah Terima')
                                                ->disk('documents')
                                                ->directory('submissions/book_donations')
                                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                                ->maxSize(10240),
                                        ]),
                                ]),
                            ]),
                        ]),
                ])
                    ->livewireSubmitHandler('submitBooks')
                    ->footer(
                        $isOpen ? [
                            Actions::make([
                                Action::make('submitBooksBtn')
                                    ->label('Simpan Seluruh Daftar Sumbangan Buku')
                                    ->submit('submitBooks')
                                    ->size('lg'),
                            ]),
                        ] : []
                    ),
            ]);
    }

    public function submitKp(): void
    {
        $distributionService = app(DocumentDistributionService::class);
        $isRevision = $this->kpSubmission?->isRevision();
        if (! $distributionService->isPeriodOpen(DocumentSubmission::TYPE_INTERNSHIP_REPORT) && ! $isRevision) {
            Notification::make()->title('Periode pendaftaran KP ditutup')->danger()->body($distributionService->periodClosedMessage())->send();

            return;
        }

        $this->saveSubmission(DocumentSubmission::TYPE_INTERNSHIP_REPORT, $this->data['kp']);
    }

    public function submitSkripsi(): void
    {
        $distributionService = app(DocumentDistributionService::class);
        $isRevision = $this->skripsiSubmission?->isRevision();
        if (! $distributionService->isPeriodOpen(DocumentSubmission::TYPE_SKRIPSI) && ! $isRevision) {
            Notification::make()->title('Periode pengajuan skripsi ditutup')->danger()->body($distributionService->periodClosedMessage())->send();

            return;
        }

        $this->saveSubmission(DocumentSubmission::TYPE_SKRIPSI, $this->data['skripsi']);
    }

    public function submitBooks(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $distributionService = app(DocumentDistributionService::class);
        if (! $distributionService->isPeriodOpen(DocumentSubmission::TYPE_BOOK_DONATION)) {
            Notification::make()->title('Periode sumbangan buku ditutup')->danger()->body($distributionService->periodClosedMessage())->send();

            return;
        }

        $items = $this->data['books']['items'] ?? [];
        if (empty($items)) {
            Notification::make()->title('Daftar buku sumbangan kosong')->warning()->send();

            return;
        }

        foreach ($items as $item) {
            if (empty($item['title'])) {
                continue;
            }

            // Backend Security: Abaikan jika item buku sudah disetujui sebelumnya
            if (! empty($item['id'])) {
                $existingItem = DocumentSubmission::query()->where('id', $item['id'])->where('user_id', $user->id)->first();
                if ($existingItem && $existingItem->isApproved()) {
                    continue;
                }
            }

            $coverPath = is_array($item['cover_image'] ?? null)
                ? array_values($item['cover_image'])[0] ?? null
                : $item['cover_image'] ?? null;

            $endorsementPath = is_array($item['endorsement_file_path'] ?? null)
                ? array_values($item['endorsement_file_path'])[0] ?? null
                : $item['endorsement_file_path'] ?? null;

            $existingBookId = ($item['book_source'] ?? 'existing') === 'existing' && ! empty($item['existing_book_id'])
                ? (int) $item['existing_book_id']
                : null;

            $payload = [
                'user_id' => $user->id,
                'type' => DocumentSubmission::TYPE_BOOK_DONATION,
                'submittable_type' => $existingBookId ? Book::class : null,
                'submittable_id' => $existingBookId,
                'title' => $item['title'],
                'subtitle' => $item['subtitle'] ?? null,
                'slug' => $item['slug'] ?? Book::generateSlugPreview($item['title']),
                'description' => $item['description'] ?? null,
                'isbn' => $item['isbn'] ?? null,
                'issn' => $item['issn'] ?? null,
                'ddc_code' => $item['ddc_code'] ?? null,
                'language' => $item['language'] ?? 'Indonesia',
                'publisher_id' => $item['publisher_id'] ?? null,
                'year' => (int) ($item['published_year'] ?? date('Y')),
                'edition' => $item['edition'] ?? null,
                'pages' => $item['pages'] ?? null,
                'author_ids' => $item['authors'] ?? [],
                'category_ids' => $item['categories'] ?? [],
                'book_condition' => $item['book_condition'] ?? 'good',
                'copies_count' => (int) ($item['copies_count'] ?? 1),
                'cover_image' => $coverPath,
                'endorsement_file_path' => $endorsementPath,
                'status' => DocumentSubmission::STATUS_PENDING,
                'revision_notes' => null,
            ];

            if (! empty($item['id'])) {
                DocumentSubmission::query()->where('id', $item['id'])->where('user_id', $user->id)->update($payload);
            } else {
                DocumentSubmission::query()->create($payload);
            }
        }

        Notification::make()
            ->title('Daftar sumbangan buku berhasil disimpan')
            ->body('Data telah diajukan dan sedang menunggu verifikasi oleh petugas.')
            ->success()
            ->send();

        $this->mount();
    }

    protected function saveSubmission(string $type, array $data): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $existing = DocumentSubmission::query()->where('user_id', $user->id)->where('type', $type)->first();
        if ($existing && $existing->isApproved()) {
            Notification::make()->title('Pengajuan telah disetujui')->danger()->body('Data resmi telah disetujui dan tidak dapat diubah lagi.')->send();

            return;
        }

        $docPath = is_array($data['document_file_path'] ?? null)
            ? array_values($data['document_file_path'])[0] ?? null
            : $data['document_file_path'] ?? null;

        $endorsementPath = is_array($data['endorsement_file_path'] ?? null)
            ? array_values($data['endorsement_file_path'])[0] ?? null
            : $data['endorsement_file_path'] ?? null;

        $payload = [
            'user_id' => $user->id,
            'type' => $type,
            'title' => $data['title'],
            'company_name' => $data['company_name'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'academic_advisor' => $data['academic_advisor'] ?? null,
            'field_advisor' => $data['field_advisor'] ?? null,
            'year' => (int) ($data['year'] ?? date('Y')),
            'abstract' => $data['abstract'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'document_file_path' => $docPath,
            'endorsement_file_path' => $endorsementPath,
            'status' => DocumentSubmission::STATUS_PENDING,
            'revision_notes' => null,
        ];

        DocumentSubmission::query()->updateOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            $payload
        );

        Notification::make()
            ->title('Pengajuan berhasil disimpan')
            ->body('Data berhasil dikirim dan sedang menunggu verifikasi petugas.')
            ->success()
            ->send();

        $this->mount();
    }

    protected static function createPublisher(array $data): int
    {
        $publisher = Publisher::query()->create([
            'name' => $data['name'],
            'slug' => Publisher::generateUniqueSlug($data['name']),
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return $publisher->getKey();
    }

    protected static function createAuthor(array $data): int
    {
        $author = Author::query()->create([
            'name' => $data['name'],
            'slug' => Author::generateUniqueSlug($data['name']),
            'email' => $data['email'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        return $author->getKey();
    }

    protected static function createCategory(array $data): int
    {
        $category = Category::query()->create([
            'name' => $data['name'],
            'slug' => Category::generateUniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return $category->getKey();
    }
}
