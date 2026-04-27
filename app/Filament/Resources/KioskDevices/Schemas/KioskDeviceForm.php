<?php

namespace App\Filament\Resources\KioskDevices\Schemas;

use App\Models\KioskDevice;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KioskDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Perangkat')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Perangkat')
                            ->maxLength(255),
                        TextInput::make('kiosk_identifier')
                            ->label('Kiosk ID')
                            ->readOnly()
                            ->copyable(copyMessage: 'Kiosk ID disalin')
                            ->dehydrated(false),
                        TextInput::make('registration_code')
                            ->label('Kode Registrasi')
                            ->readOnly()
                            ->copyable(copyMessage: 'Kode registrasi disalin')
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Status')
                    ->schema([
                        Placeholder::make('status')
                            ->label('Status Saat Ini')
                            ->content(fn ($record) => match ($record?->status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'revoked' => 'Dicabut',
                                default => 'Menunggu persetujuan',
                            }),
                        Placeholder::make('connectivity_status')
                            ->label('Status Konektivitas')
                            ->content(fn (?KioskDevice $record): string => match ($record?->connectivityStatus()) {
                                'online' => 'Online',
                                'stale' => 'Tidak Stabil',
                                default => 'Offline',
                            }),
                        Placeholder::make('approved_at')
                            ->label('Disetujui Pada')
                            ->content(fn ($record) => $record?->approved_at?->translatedFormat('d M Y H:i') ?? '-'),
                        Placeholder::make('approver')
                            ->label('Disetujui Oleh')
                            ->content(fn (?KioskDevice $record): string => $record?->approver?->name ?? '-'),
                        Placeholder::make('revoked_at')
                            ->label('Dicabut Pada')
                            ->content(fn (?KioskDevice $record): string => $record?->revoked_at?->translatedFormat('d M Y H:i') ?? '-'),
                        Placeholder::make('last_seen_at')
                            ->label('Terakhir Terlihat')
                            ->content(fn ($record) => $record?->last_seen_at?->translatedFormat('d M Y H:i') ?? '-'),
                        Placeholder::make('ip_address')
                            ->label('IP Terakhir')
                            ->content(fn ($record) => $record?->ip_address ?? '-'),
                        Placeholder::make('user_agent')
                            ->label('User Agent')
                            ->content(fn ($record) => $record?->user_agent ?? '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
