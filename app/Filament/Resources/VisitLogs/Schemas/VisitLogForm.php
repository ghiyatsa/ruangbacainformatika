<?php

namespace App\Filament\Resources\VisitLogs\Schemas;

use App\Models\VisitLog;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Pengunjung')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Select::make('visitor_type')
                            ->label('Jenis Pengunjung')
                            ->options(VisitLog::visitorTypeOptions())
                            ->required(),
                        TextInput::make('identity_number')
                            ->label('NIM / NIP / Nomor Identitas')
                            ->maxLength(50),
                        TextInput::make('institution')
                            ->label('Prodi / Instansi')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),
                Section::make('Detail Kunjungan')
                    ->schema([
                        Select::make('purpose')
                            ->label('Tujuan Kunjungan')
                            ->options(VisitLog::purposeOptions())
                            ->required(),
                        Placeholder::make('visited_at')
                            ->label('Waktu Kunjungan')
                            ->content(fn ($record) => $record?->visited_at?->translatedFormat('d M Y H:i') ?? '-'),
                        Placeholder::make('kioskDevice.name')
                            ->label('Perangkat Kiosk')
                            ->content(fn ($record) => $record?->kioskDevice?->name ?? 'Tidak tercatat'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
