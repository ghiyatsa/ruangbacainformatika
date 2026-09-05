<?php

namespace App\Filament\Resources\DocumentSubmissions\Schemas;

use App\Models\DocumentSubmission;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan & Mahasiswa')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Tipe Pengajuan')
                                    ->badge()
                                    ->state(fn (DocumentSubmission $record): string => $record->typeLabel())
                                    ->color(fn (DocumentSubmission $record): string => $record->typeBadgeColor()),
                                TextEntry::make('user.name')
                                    ->label('Nama Mahasiswa'),
                                TextEntry::make('nim')
                                    ->label('NIM')
                                    ->state(fn (DocumentSubmission $record): string => $record->user?->identityNumber() ?? '-')
                                    ->placeholder('-'),
                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),
                                TextEntry::make('title')
                                    ->label('Judul / Dokumen')
                                    ->columnSpanFull(),
                                TextEntry::make('company_name')
                                    ->label('Instansi / Perusahaan')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT),
                                TextEntry::make('company_address')
                                    ->label('Alamat Instansi')
                                    ->placeholder('-')
                                    ->columnSpan(2)
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT),
                                TextEntry::make('academic_advisor')
                                    ->label('Dosen Pembimbing')
                                    ->visible(fn (DocumentSubmission $record): bool => in_array($record->type, [DocumentSubmission::TYPE_INTERNSHIP_REPORT, DocumentSubmission::TYPE_SKRIPSI])),
                                TextEntry::make('field_advisor')
                                    ->label('Pembimbing Lapangan')
                                    ->placeholder('-')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT),
                                TextEntry::make('publisher.name')
                                    ->label('Penerbit Buku')
                                    ->placeholder('-')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION),
                                TextEntry::make('isbn')
                                    ->label('ISBN')
                                    ->placeholder('-')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION),
                                TextEntry::make('issn')
                                    ->label('ISSN')
                                    ->placeholder('-')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION && filled($record->issn)),
                                TextEntry::make('ddc_code')
                                    ->label('Kode DDC')
                                    ->placeholder('-')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION && filled($record->ddc_code)),
                                TextEntry::make('copies_count')
                                    ->label('Jumlah Eksemplar')
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION),
                                TextEntry::make('book_condition')
                                    ->label('Kondisi Fisik')
                                    ->state(fn (DocumentSubmission $record): string => match ($record->book_condition) {
                                        'good' => 'Sangat Baik / Baru',
                                        'fair' => 'Baik / Layak Baca',
                                        default => $record->book_condition ?? '-',
                                    })
                                    ->visible(fn (DocumentSubmission $record): bool => $record->type === DocumentSubmission::TYPE_BOOK_DONATION && filled($record->book_condition)),
                                TextEntry::make('year')
                                    ->label('Tahun'),
                                TextEntry::make('keywords')
                                    ->label('Kata Kunci')
                                    ->placeholder('-')
                                    ->columnSpanFull()
                                    ->visible(fn (DocumentSubmission $record): bool => in_array($record->type, [DocumentSubmission::TYPE_INTERNSHIP_REPORT, DocumentSubmission::TYPE_SKRIPSI])),
                                TextEntry::make('abstract')
                                    ->label('Abstrak')
                                    ->placeholder('-')
                                    ->columnSpanFull()
                                    ->visible(fn (DocumentSubmission $record): bool => in_array($record->type, [DocumentSubmission::TYPE_INTERNSHIP_REPORT, DocumentSubmission::TYPE_SKRIPSI])),
                            ]),
                    ]),
                Section::make('Status Verifikasi & Dokumen')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->state(fn (DocumentSubmission $record): string => $record->statusLabel())
                                    ->badge()
                                    ->color(fn (DocumentSubmission $record): string => $record->statusColor()),
                                TextEntry::make('receipt_number')
                                    ->label('Nomor Tanda Terima')
                                    ->placeholder('-'),
                                TextEntry::make('reviewer.name')
                                    ->label('Diverifikasi Oleh')
                                    ->placeholder('-'),
                                TextEntry::make('reviewed_at')
                                    ->label('Waktu Verifikasi')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('revision_notes')
                                    ->label('Catatan Revisi')
                                    ->placeholder('-')
                                    ->columnSpan(2)
                                    ->visible(fn (DocumentSubmission $record): bool => ! empty($record->revision_notes)),
                                TextEntry::make('created_at')
                                    ->label('Tanggal Pengajuan')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),
            ]);
    }
}
