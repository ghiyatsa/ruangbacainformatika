<?php

namespace App\Filament\Resources\VisitLogs\Schemas;

use App\Models\VisitLog;
use Carbon\CarbonInterface;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Pengunjung')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Lengkap')
                                    ->weight('bold'),
                                TextEntry::make('visitor_type')
                                    ->label('Jenis Pengunjung')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => VisitLog::visitorTypeOptions()[$state] ?? $state),
                                TextEntry::make('identity_number')
                                    ->label('NIM / NIP / Nomor Identitas')
                                    ->placeholder('-'),
                                TextEntry::make('institution')
                                    ->label('Prodi / Instansi')
                                    ->placeholder('-'),
                                TextEntry::make('phone')
                                    ->label('WhatsApp')
                                    ->copyable()
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Detail Kunjungan')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                TextEntry::make('purpose')
                                    ->label('Tujuan Kunjungan')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => VisitLog::purposeOptions()[$state] ?? $state),
                                TextEntry::make('visited_at')
                                    ->label('Waktu Kunjungan')
                                    ->formatStateUsing(
                                        fn (?CarbonInterface $state): string => $state?->copy()->setTimezone(VisitLog::adminTimezone())->translatedFormat('d M Y H:i') ?? '-',
                                    ),
                                TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ]),
            ]);
    }
}
