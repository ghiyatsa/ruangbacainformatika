<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

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
                    ->placeholder('Nama lengkap'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn ($record) => $record !== null)
                    ->helperText(fn ($record): ?string => $record !== null
                        ? 'Email tidak dapat diubah.'
                        : 'Email ini digunakan untuk masuk dengan Google.'),
                Select::make('roles')
                    ->label('Peran')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->helperText('Pilih peran sesuai kewenangan akun'),
                Toggle::make('is_approved')
                    ->label('Lolos Review Awal')
                    ->helperText('Tandai jika akun sudah lolos review awal. Akses pinjam tetap menunggu verifikasi WhatsApp dan peran anggota.')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger'),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->nullable()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (Get $get, ?Model $record): bool => filled($record?->whatsapp_verified_at) && ($get('whatsapp_verified_at') ?? true))
                    ->placeholder('0812xxxxxx'),
                Toggle::make('whatsapp_verified_at')
                    ->label('WhatsApp Terverifikasi')
                    ->formatStateUsing(fn (?Model $record): bool => $record?->hasVerifiedWhatsApp() ?? false)
                    ->dehydrateStateUsing(fn (bool $state) => $state ? now() : null)
                    ->live()
                    ->helperText('Tandai jika nomor WhatsApp sudah diverifikasi oleh petugas tanpa OTP'),
                Textarea::make('address')
                    ->label('Alamat')
                    ->nullable()
                    ->maxLength(1000)
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Alamat lengkap'),
            ]);
    }
}
