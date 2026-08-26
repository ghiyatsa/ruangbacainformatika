<?php

namespace App\Filament\Resources\WhatsAppMessageLogs\Schemas;

use App\Models\WhatsAppMessageLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WhatsAppMessageLogsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Pengguna')
                    ->placeholder('-'),
                TextEntry::make('phone_number_masked')
                    ->label('Nomor (disamarkan)'),
                TextEntry::make('category')
                    ->label('Kategori'),
                TextEntry::make('notification_type')
                    ->label('Tipe Notifikasi')
                    ->badge(),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (WhatsAppMessageLog $record): string => match ($record->status) {
                        WhatsAppMessageLog::StatusSent => 'success',
                        WhatsAppMessageLog::StatusPending => 'warning',
                        WhatsAppMessageLog::StatusFailed => 'danger',
                        WhatsAppMessageLog::StatusSkipped => 'gray',
                        default => 'gray',
                    }),
                TextEntry::make('attempts')
                    ->label('Percobaan'),
                TextEntry::make('provider_status')
                    ->label('Status Provider')
                    ->placeholder('-'),
                TextEntry::make('provider_message_id')
                    ->label('ID Pesan Provider')
                    ->placeholder('-'),
                TextEntry::make('sent_at')
                    ->label('Terkirim')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextEntry::make('failed_at')
                    ->label('Gagal')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextEntry::make('skipped_at')
                    ->label('Dilewati')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextEntry::make('message_preview')
                    ->label('Pratinjau Pesan')
                    ->columnSpanFull(),
                TextEntry::make('error_message')
                    ->label('Error')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
