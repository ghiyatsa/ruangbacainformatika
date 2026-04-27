<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\Loan;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Member'),
                        TextEntry::make('user.email')
                            ->label('Email Member')
                            ->copyable(),
                        TextEntry::make('kioskDevice.name')
                            ->label('Perangkat Kiosk')
                            ->placeholder('Tidak tercatat'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Loan::statusOptions()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                Loan::STATUS_BORROWED => 'warning',
                                Loan::STATUS_RETURNED => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('items_count')
                            ->label('Total Item'),
                        TextEntry::make('active_items_count')
                            ->label('Item Belum Kembali'),
                        TextEntry::make('borrowed_at')
                            ->label('Waktu Pinjam')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('due_at')
                            ->label('Jatuh Tempo')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('returned_at')
                            ->label('Waktu Selesai')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
