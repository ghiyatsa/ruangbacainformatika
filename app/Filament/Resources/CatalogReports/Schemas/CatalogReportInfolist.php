<?php

namespace App\Filament\Resources\CatalogReports\Schemas;

use App\Models\CatalogReport;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatalogReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Ringkasan Laporan')
                            ->schema([
                                TextEntry::make('catalog_type')
                                    ->label('Jenis katalog')
                                    ->formatStateUsing(fn (CatalogReport $record): string => $record->catalogTypeLabel())
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('catalog_title')
                                    ->label('Judul data')
                                    ->url(fn (CatalogReport $record): ?string => $record->publicUrl(), shouldOpenInNewTab: true),
                                TextEntry::make('catalog_url')
                                    ->label('URL')
                                    ->url(fn (CatalogReport $record): ?string => $record->publicUrl(), shouldOpenInNewTab: true)
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label('Dilaporkan pada')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('message')
                                    ->label('Isi laporan')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),
                        Section::make('Pelapor & Tindak Lanjut')
                            ->schema([
                                TextEntry::make('reporter_display_name')
                                    ->label('Nama pelapor'),
                                TextEntry::make('reporter_email')
                                    ->label('Email pelapor')
                                    ->placeholder('-'),
                                TextEntry::make('user.email')
                                    ->label('Email akun')
                                    ->placeholder('-'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn (CatalogReport $record): string => $record->statusLabel())
                                    ->badge()
                                    ->color(fn (CatalogReport $record): string => $record->statusColor()),
                                TextEntry::make('reviewed_at')
                                    ->label('Ditinjau pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('admin_notes')
                                    ->label('Catatan admin')
                                    ->placeholder('Belum ada catatan admin.')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
