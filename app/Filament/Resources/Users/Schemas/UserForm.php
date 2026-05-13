<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
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
                    ->helperText(fn ($record): ?string => $record !== null ? 'Email tidak dapat diubah.' : null),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->required(fn ($record): bool => $record === null)
                    ->minLength(8)
                    ->maxLength(255)
                    ->hidden(fn ($record): bool => $record !== null)
                    ->helperText('Minimal 8 karakter.'),
                Select::make('roles')
                    ->label('Peran')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->helperText('Pilih sesuai hak akses.'),
                Toggle::make('is_approved')
                    ->label('Akun Disetujui')
                    ->helperText('Aktifkan jika akun siap dipakai.')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger'),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->nullable()
                    ->unique(ignoreRecord: true)
                    ->placeholder('0812xxxxxx'),
            ]);
    }
}
