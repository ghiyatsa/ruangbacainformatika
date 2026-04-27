<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\Loan;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Peminjaman')
                    ->schema([
                        Placeholder::make('user.name')
                            ->label('Member')
                            ->content(fn (?Loan $record): string => $record?->user?->name ?? '-'),
                        Placeholder::make('user.email')
                            ->label('Email Member')
                            ->content(fn (?Loan $record): string => $record?->user?->email ?? '-'),
                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn (?Loan $record): string => Loan::statusOptions()[$record?->status ?? ''] ?? '-'),
                        Placeholder::make('borrowed_at')
                            ->label('Waktu Pinjam')
                            ->content(fn (?Loan $record): string => $record?->borrowed_at?->translatedFormat('d M Y H:i') ?? '-'),
                    ])
                    ->columns(2),
            ]);
    }
}
