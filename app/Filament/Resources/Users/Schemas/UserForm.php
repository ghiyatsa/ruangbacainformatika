<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Masukkan nama lengkap pengguna'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn ($record) => $record !== null)
                    ->helperText(fn ($record): ?string => $record !== null
                        ? 'Email tidak dapat diubah.'
                        : 'Pengguna akan masuk dengan akun Google pada email ini.'),
                Select::make('roles')
                    ->label('Peran')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->helperText('Pilih sesuai hak akses.'),
                Toggle::make('is_approved')
                    ->label('Akun Disetujui')
                    ->helperText('Aktifkan jika akun anggota sudah lolos pengecekan operator.')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger'),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->nullable()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn ($record): bool => filled($record?->whatsapp_verified_at))
                    ->placeholder('0812xxxxxx'),
                Textarea::make('address')
                    ->label('Alamat')
                    ->nullable()
                    ->maxLength(1000)
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Masukkan alamat lengkap pengguna'),
            ]);
    }
}
