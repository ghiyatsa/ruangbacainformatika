<?php

namespace App\Filament\Resources\DocumentSubmissions\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\DocumentSubmission;
use App\Services\BookCoverImageService;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Informasi Pengajuan & Mahasiswa')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextEntry::make('type')
                                                    ->label('Tipe Pengajuan')
                                                    ->badge()
                                                    ->state(fn (DocumentSubmission $record): string => $record->typeLabel())
                                                    ->color(fn (DocumentSubmission $record): string => $record->typeBadgeColor()),

                                                TextEntry::make('submission_batch_token')
                                                    ->label('Batch ID')
                                                    ->badge()
                                                    ->color('gray')
                                                    ->placeholder('Mandiri')
                                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION && filled($record->submission_batch_token)),

                                                TextEntry::make('user.name')
                                                    ->label('Nama Mahasiswa'),

                                                TextEntry::make('nim')
                                                    ->label('NIM')
                                                    ->state(fn (DocumentSubmission $record): string => $record->user?->identityNumber() ?? '-')
                                                    ->placeholder('-'),

                                                TextEntry::make('user.email')
                                                    ->label('Email Mahasiswa')
                                                    ->copyable(),

                                                TextEntry::make('year')
                                                    ->label('Tahun Pelaksanaan / Terbit')
                                                    ->badge()
                                                    ->color('gray'),
                                            ]),

                                        TextEntry::make('title')
                                            ->label('Judul Utama Dokumen / Buku')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->columnSpanFull(),

                                        TextEntry::make('subtitle')
                                            ->label('Subjudul')
                                            ->placeholder('-')
                                            ->columnSpanFull()
                                            ->visible(fn (DocumentSubmission $record): bool => filled($record->subtitle)),

                                        TextEntry::make('company_name')
                                            ->label('Instansi / Perusahaan Tempat KP')
                                            ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT),

                                        TextEntry::make('company_address')
                                            ->label('Alamat Instansi')
                                            ->placeholder('-')
                                            ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT),

                                        TextEntry::make('academic_advisor')
                                            ->label('Dosen Pembimbing')
                                            ->visible(fn (DocumentSubmission $record): bool => in_array($record->type, [DocumentSubmission::TYPE_INTERNSHIP_REPORT, DocumentSubmission::TYPE_SKRIPSI])),

                                        TextEntry::make('field_advisor')
                                            ->label('Pembimbing Lapangan Instansi')
                                            ->placeholder('-')
                                            ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT),

                                        TextEntry::make('abstract')
                                            ->label('Abstrak / Ringkasan Dokumen')
                                            ->placeholder('-')
                                            ->columnSpanFull()
                                            ->visible(fn (DocumentSubmission $record): bool => in_array($record->type, [DocumentSubmission::TYPE_INTERNSHIP_REPORT, DocumentSubmission::TYPE_SKRIPSI])),

                                        TextEntry::make('keywords')
                                            ->label('Kata Kunci')
                                            ->placeholder('-')
                                            ->columnSpanFull()
                                            ->visible(fn (DocumentSubmission $record): bool => in_array($record->type, [DocumentSubmission::TYPE_INTERNSHIP_REPORT, DocumentSubmission::TYPE_SKRIPSI])),

                                        TextEntry::make('description')
                                            ->label('Sinopsis Buku')
                                            ->placeholder('-')
                                            ->columnSpanFull()
                                            ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION && filled($record->description)),
                                    ]),

                                Section::make('Detail Bibliografi Buku')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION)
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextEntry::make('publisher.name')
                                                    ->label('Penerbit')
                                                    ->state(fn (DocumentSubmission $record): string => $record->publisher?->name ?? $record->publisher_name ?? '-')
                                                    ->placeholder('-'),

                                                TextEntry::make('edition')
                                                    ->label('Edisi / Volume')
                                                    ->placeholder('-'),

                                                TextEntry::make('isbn')
                                                    ->label('ISBN')
                                                    ->copyable()
                                                    ->placeholder('-'),

                                                TextEntry::make('issn')
                                                    ->label('ISSN')
                                                    ->copyable()
                                                    ->placeholder('-')
                                                    ->visible(fn (DocumentSubmission $record): bool => filled($record->issn)),

                                                TextEntry::make('ddc_code')
                                                    ->label('Kode DDC')
                                                    ->placeholder('-')
                                                    ->visible(fn (DocumentSubmission $record): bool => filled($record->ddc_code)),

                                                TextEntry::make('language')
                                                    ->label('Bahasa Dokumen')
                                                    ->default('Indonesia'),

                                                TextEntry::make('pages')
                                                    ->label('Jumlah Halaman')
                                                    ->placeholder('-'),

                                                TextEntry::make('copies_count')
                                                    ->label('Jumlah Eksemplar Disumbangkan')
                                                    ->weight('bold')
                                                    ->badge()
                                                    ->color('primary')
                                                    ->formatStateUsing(fn ($state) => ($state ?: 1).' Eksemplar'),

                                                TextEntry::make('authors')
                                                    ->label('Penulis Terdaftar')
                                                    ->badge()
                                                    ->color('warning')
                                                    ->state(function (DocumentSubmission $record): array {
                                                        if (! empty($record->author_ids)) {
                                                            return Author::query()->whereIn('id', $record->author_ids)->pluck('name')->all();
                                                        }
                                                        if (filled($record->author_names)) {
                                                            return [$record->author_names];
                                                        }

                                                        return [];
                                                    })
                                                    ->placeholder('Tidak ada penulis'),

                                                TextEntry::make('categories')
                                                    ->label('Kategori Buku')
                                                    ->badge()
                                                    ->color('success')
                                                    ->state(function (DocumentSubmission $record): array {
                                                        if (! empty($record->category_ids)) {
                                                            return Category::query()->whereIn('id', $record->category_ids)->pluck('name')->all();
                                                        }

                                                        return [];
                                                    })
                                                    ->placeholder('Tidak ada kategori'),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 2]),

                        Group::make()
                            ->schema([
                                Section::make('Cover Buku')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION)
                                    ->schema([
                                        ImageEntry::make('cover_image')
                                            ->hiddenLabel()
                                            ->alignCenter()
                                            ->defaultImageUrl(app(BookCoverImageService::class)->getDefaultCoverUrl())
                                            ->disk('public')
                                            ->imageWidth('100%')
                                            ->imageHeight('auto'),
                                    ]),

                                Section::make('Status & Lembar Verifikasi')
                                    ->schema([
                                        TextEntry::make('status')
                                            ->label('Status Pengajuan')
                                            ->state(fn (DocumentSubmission $record): string => $record->statusLabel())
                                            ->badge()
                                            ->color(fn (DocumentSubmission $record): string => $record->statusColor()),

                                        TextEntry::make('receipt_number')
                                            ->label('Nomor Tanda Terima')
                                            ->weight('bold')
                                            ->copyable()
                                            ->placeholder('Belum Diterbitkan'),

                                        TextEntry::make('reviewer.name')
                                            ->label('Petugas Verifikasi')
                                            ->placeholder('-'),

                                        TextEntry::make('reviewed_at')
                                            ->label('Waktu Persetujuan')
                                            ->dateTime('d F Y, H:i')
                                            ->placeholder('-'),

                                        TextEntry::make('revision_notes')
                                            ->label('Catatan Revisi dari Petugas')
                                            ->color('danger')
                                            ->placeholder('-')
                                            ->visible(fn (DocumentSubmission $record): bool => filled($record->revision_notes)),

                                        TextEntry::make('created_at')
                                            ->label('Waktu Diajukan')
                                            ->dateTime('d F Y, H:i'),

                                        TextEntry::make('updated_at')
                                            ->label('Pembaruan Terakhir')
                                            ->dateTime('d F Y, H:i'),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }
}
