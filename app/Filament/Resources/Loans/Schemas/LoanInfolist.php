<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\User;
use App\Support\LoanConsequenceService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Anggota')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Anggota'),
                        TextEntry::make('email')
                            ->label('Email Anggota')
                            ->copyable(),
                        TextEntry::make('whatsapp')
                            ->label('WhatsApp')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('active_loans_count')
                            ->label('Transaksi Aktif')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('borrowing_access')
                            ->label('Status Peminjaman')
                            ->state(fn (User $record): string => app(LoanConsequenceService::class)->borrowingAccessSummary($record)['label'])
                            ->badge()
                            ->color(fn (User $record): string => app(LoanConsequenceService::class)->borrowingAccessSummary($record)['color']),
                        TextEntry::make('borrowing_access_detail')
                            ->label('Keterangan')
                            ->state(fn (User $record): string => app(LoanConsequenceService::class)->borrowingAccessSummary($record)['detail'] ?? '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
