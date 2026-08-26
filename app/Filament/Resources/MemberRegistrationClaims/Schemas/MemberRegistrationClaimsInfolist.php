<?php

namespace App\Filament\Resources\MemberRegistrationClaims\Schemas;

use App\Models\MemberRegistrationClaim;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MemberRegistrationClaimsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nama'),
                TextEntry::make('email')
                    ->label('Email')
                    ->copyable(),
                TextEntry::make('whatsapp')
                    ->label('WhatsApp'),
                TextEntry::make('address')
                    ->label('Alamat'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => ucfirst($state),
                    )
                    ->color(fn (MemberRegistrationClaim $record): string => match ($record->status) {
                        MemberRegistrationClaim::STATUS_PENDING => 'warning',
                        MemberRegistrationClaim::STATUS_LINKED => 'info',
                        MemberRegistrationClaim::STATUS_CLAIMED => 'success',
                        MemberRegistrationClaim::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('user.name')
                    ->label('Akun Google Terautasi')
                    ->placeholder('Belum ditautkan'),
                TextEntry::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i'),
                TextEntry::make('claimed_at')
                    ->label('Diklaim')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextEntry::make('last_error_message')
                    ->label('Error Terakhir')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
